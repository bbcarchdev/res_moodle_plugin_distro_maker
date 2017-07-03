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
 * A LODResponse is used by a LOD to encapsulate an HTTP response so
 * that it can be processed into its index. LODResponses instances are created,
 * processed, and destroyed automatically as part of LOD::fetch().
 *
 * All properties are public and intended to be set directly, so remove
 * phpcheckstyle warnings about them being unused.
 * @SuppressWarnings checkUnusedVariables
 */
class LODResponse
{
    /**
     * HTTP status of the response.
     * @property int $status
     */
    public $status = 0;

    /**
     * Error code for the response (typically 1 if an error occurred). If this
     * is 0, no error occurred.
     * @property int $error
     */
    public $error = 0;

    /**
     * Error message for the response; if the error happened on the HTTP side
     * (e.g. 500 error), this is typically the reason phrase from the HTTP
     * response.
     * @property string $errMsg
     */
    public $errMsg = NULL;

    /**
     * The URI which was originally requested. If the response was redirected,
     * this remains set to the original URI.
     */
    public $target = NULL;

    /**
     * Content location, either from the 'Content-Location' header (if set)
     * or the URI (if no 'Content-Location' is available).
     * @property string $contentLocation
     */
    public $contentLocation = NULL;

    /**
     * Content type of the response, e.g. 'text/turtle', 'application/rdf+xml'
     * @property string $type
     */
    public $type = NULL;

    /**
     * Response body.
     * @property string $payload
     */
    public $payload = NULL;
}
