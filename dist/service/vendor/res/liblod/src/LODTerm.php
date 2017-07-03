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

/**
 * Abstract class for RDF terms (URIs and literals).
 */
abstract class LODTerm
{
    /**
     * Value for the term.
     * @property string $value
     */
    public $value;

    /**
     * Constructor.
     * @param string $value Sets the value for the term
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Check whether this term is a resource or not.
     * @return bool TRUE if term is a resource, FALSE otherwise.
     */
    public abstract function isResource();

    /**
     * Get string representation of term.
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}
