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

// API example provided by Mo McRoberts
// Get the topic "Oxford" and show related web pages
require_once(__DIR__ . '/../vendor/autoload.php');

use res\liblod\LOD;

$lod = new LOD();
$lod->setPrefix('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
$lod->setPrefix('foaf', 'http://xmlns.com/foaf/0.1/');

$uri = 'http://www.dbpedialite.org/things/22308#id';

$inst = $lod->resolve($uri);
if($inst === false)
{
  trigger_error("Could not retrieve URI $uri");
}
echo '<h1>' . htmlspecialchars($inst['rdfs:label']) . '</h1>';
echo '<ul>';
foreach($inst['foaf:primaryTopicOf,foaf:page'] as $url)
{
  $str = htmlspecialchars($url);
  echo '<li><a href="' . $str . '">' . $str . '</a></li>';
}
echo '</ul>';
?>
