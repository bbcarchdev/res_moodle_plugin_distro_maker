<?php
/*
 * liblod-php - a Linked Open Data client library for PHP
 * Copyright (C) 2017 Elliot Smith
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace res\liblod;

use res\liblod\Rdf;
use res\liblod\LODResource;
use res\liblod\LODStatement;

use pietercolpaert\hardf\N3Parser;
use \EasyRdf_Parser_RdfXml;
use \EasyRdf_Graph;

/**
 * Wrapper round hardf and EasyRDF parsers
 * (hardf Turtle parser is much faster than EasyRDF but hardf doesn't have
 * an RDF/XML parser).
 */
class Parser
{
    /**
     * RDF helper
     * @property res\liblod\Rdf $rdf
     */
    private $rdf;

    /**
     * Constructor.
     * @param res\liblod\Rdf Sets RDF helper for this parser
     */
    public function __construct($rdf = NULL)
    {
        $this->rdf = (empty($rdf) ? new Rdf() : $rdf);
    }

    /**
     * Parse Turtle or RDF/XML into LODStatements.
     *
     * @param string $rdf RDF to parse
     * @param string $type MIME type of the response being parsed; one of
     * text/turtle, application/rdf+xml; if $type is not a recognised content
     * type, throws an exception
     *
     * @throws Exception (if RDF can't be parsed)
     *
     * @return array Array of res\liblod\LODStatement objects
     */
    public function parse($rdf, $type)
    {
        if(preg_match('|^text/turtle|', $type))
        {
            $triplesOut = array();

            // hardf uses the N3.js triple format
            $parser = new N3Parser(array());
            $triples = $parser->parse($rdf);

            foreach($triples as $triple)
            {
                $subject = new LODResource($triple['subject']);
                $predicate = new LODResource($triple['predicate']);
                $object = $triple['object'];

                $obj = NULL;

                if($this->rdf->isLiteral($object))
                {
                    $value = $this->rdf->getLiteralValue($object);
                    $languageAndType = $this->rdf->getLiteralLanguageAndDatatype($object);
                    $obj = new LODLiteral($value, $languageAndType);
                }

                // fallback: object is a URI
                if(empty($obj))
                {
                    $obj = new LODResource($object);
                }

                $triplesOut[] = new LODStatement($subject, $predicate, $obj);
            }

            return $triplesOut;
        }
        else if(preg_match('|^application/rdf\+xml|', $type))
        {
            $graph = new EasyRdf_Graph();
            $parser = new EasyRdf_Parser_RdfXml();
            $parser->parse($graph, $rdf, 'rdfxml', '');
            return $this->rdf->getTriples($graph);
        }

        // if we reach here, we haven't been able to parse the RDF
        throw new Exception('No parser for content type ' . $type);
    }
}
