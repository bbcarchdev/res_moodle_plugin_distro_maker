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
// Get the mrss:player triple for a topic
require_once(__DIR__ . '/../vendor/autoload.php');

use res\liblod\LOD;

$lod = new LOD();
$lod->setPrefix('mrss', 'http://search.yahoo.com/mrss/');
$lod->languages = array('en-gb', 'en', 'en-us', 'en-au');

$uri = 'http://acropolis.org.uk/98ae9cd1e55c4055a266dcc2f9570c70#id';
if(!isset($lod[$uri]))
{
  trigger_error("Failed to fetch <$uri>");
  return;
}

$instance = $lod[$uri];

echo "instance = <$instance>\n"; // prints instance = <SUBJECT-URI>

echo "title = [" . $instance['skos:prefLabel,dct:title,rdfs:label'] . "]\n"; // prints the title

if(isset($instance['mrss:player']))
{
  echo "Playable media:\n";
  foreach($instance['mrss:player'] as $value)
  {
    // we don't care about `mrss:player` triples where the object is not a resource
    if($value->isResource())
    {
       echo "• <$value>\n";
    }
  }
}
?>
