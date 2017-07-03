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

use res\liblod\LODResource;
use res\liblod\LODLiteral;
use res\liblod\LODStatement;
use res\liblod\LODTerm;
use res\liblod\Rdf;

/**
 * Represents a single RDF statement with subject, predicate and object.
 */
class LODStatement
{
    /**
     * Subject of the statement.
     * @property res\liblod\LODResource $subject
     */
    public $subject;

    /**
     * Predicate of the statement.
     * @property res\liblod\LODResource $predicate
     */
    public $predicate;

    /**
     * Object of the statement.
     * @property res\liblod\LODTerm $object
     */
    public $object;

    /**
     * Constructor.
     *
     * @param string|res\liblod\LODResource $subj Subject of the statement
     * @param string|res\liblod\LODResource $pred Predicate of the statement
     * @param array|res\liblod\LODTerm $objOrSpec Object of the statement;
     * $objOrSpec can either be a LODTerm instance or an options array like
     * [ 'value' => 'somestring', 'type' => 'uri|literal',
     *   'datatype' => 'xsd:...' || 'lang' => 'en' ]
     * @param array $prefixes Map from prefixes to full URIs
     * @param res\liblod\Rdf $rdf RDF helper
     */
    public function __construct($subj, $pred, $objOrSpec,
    $prefixes = Rdf::COMMON_PREFIXES, $rdf = NULL)
    {
        if(empty($rdf))
        {
            $rdf = new Rdf();
        }

        if(!($subj instanceof LODResource))
        {
            $subj = new LODResource($rdf->expandPrefix($subj, $prefixes));
        }

        if(!($pred instanceof LODResource))
        {
            $pred = new LODResource($rdf->expandPrefix($pred, $prefixes));
        }

        $obj = NULL;

        // already a term
        if($objOrSpec instanceof LODTerm)
        {
            $obj = $objOrSpec;
        }

        // fallback: it's a spec for a literal or URI
        if(empty($obj))
        {
            if($objOrSpec['type'] === 'literal')
            {
                if(isset($objOrSpec['datatype']))
                {
                    $datatype = $objOrSpec['datatype'];
                    $datatypeUri = $rdf->expandPrefix($datatype, $prefixes);
                    $objOrSpec['datatype'] = $datatypeUri;

                }
                $obj = new LODLiteral($objOrSpec['value'], $objOrSpec);
            }
            else if($objOrSpec['type'] === 'uri')
            {
                $uri = $rdf->expandPrefix($objOrSpec['value'], $prefixes);
                $obj = new LODResource($uri);
            }
        }

        $this->subject = $subj;
        $this->predicate = $pred;
        $this->object = $obj;
    }

    /**
     * Get a representation of this statement as an N-Triples compatible string.
     *
     * @return string Examples:
     * <http://res/Frank> <http://www.w3.org/2000/01/rdf-schema#seeAlso> <http://res/Frankie>
     * <http://res/Frank> <http://www.w3.org/2000/01/rdf-schema#label> "Frank"@en-gb
     * <http://res/Frank> <http://xmlns.com/foaf/0.1/age>
     *     "5"^^<http://www.w3.org/2001/XMLSchema#integer>
     */
    public function __toString()
    {
        $str = '<' . $this->subject->__toString() . '> ';
        $str .= '<' . $this->predicate->__toString() . '> ';

        // uri
        if($this->object->isResource())
        {
            return $str . '<' . $this->object->__toString() . '>';
        }

        // literal
        $objStr = json_encode($this->object->__toString());

        if($this->object->language)
        {
            $objStr .= '@' . $this->object->language;
        }
        else if($this->object->datatype)
        {
            $objStr .= '^^<' . $this->object->datatype . '>';
        }

        return $str . $objStr;
    }

    /**
     * Generate a key which uniquely-identifies the statement, using
     * a hash of its subject+predicate+object values + object datatype/lang.
     *
     * @return string Hash unique to a statement with this subject, predicate
     * and object
     */
    public function getKey()
    {
        return md5($this->__toString());
    }
}
