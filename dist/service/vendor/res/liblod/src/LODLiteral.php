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

use res\liblod\LODTerm;

/**
 * An RDF literal.
 */
class LODLiteral extends LODTerm
{
    /**
     * RDF data type for the literal, in full URI form, e.g.
     * 'http://www.w3.org/2001/XMLSchema#string'.
     * @property string $datatype
     */
    public $datatype = NULL;

    /**
     * RDF language tag for the literal, e.g. 'en-gb'.
     * @property string $language
     */
    public $language = NULL;

    /**
     * Constructor.
     *
     * @param string $value Value for the literal
     * @param array $spec May contain a 'lang' or 'datatype' key;
     * 'lang' should be an RDF language tag;
     * 'datatype' should be an expanded (not prefixed) URI for the datatype
     * of the literal.
     */
    public function __construct($value, $spec = array())
    {
        parent::__construct($value);

        if(isset($spec['lang']))
        {
            $this->language = $spec['lang'];
        }
        else if(isset($spec['datatype']))
        {
            $this->datatype = $spec['datatype'];
        }
    }

    /**
     * Check whether this is a resource. Always returns FALSE.
     *
     * @return bool
     */
    public function isResource()
    {
        return FALSE;
    }
}
