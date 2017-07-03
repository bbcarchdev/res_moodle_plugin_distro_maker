<?php
/**
 * Implementation of a callback handler for accepting media item selections
 * from the RES Moodle plugin service.
 *
 * See https://docs.moodle.org/dev/Repository_plugins_embedding_external_file_chooser
 *
 * When a piece of media is selected in the RES Moodle plugin service,
 * the user's browser is forwarded to this page. The URL contains one
 * querystring variable, "media", which is a JSON-encoded representation of the
 * selected piece of media.
 *
 * An example of the decoded JSON:
 *
 * {
 *   "sourceUri":"http://bbcimages.acropolis.org.uk/6311090#id",
 *   "uri":"http://bbcimages.acropolis.org.uk/6311090/player",
 *   "mediaType":"image",
 *   "license":"",
 *   "label":"A Blue Tit visits a bird feeder",
 *   "description":"A Blue Tit bird eating nuts from a bird feeder.",
 *   "thumbnail":"http://bbcimages.acropolis.org.uk/6311090/media/6311090-200x200.jpeg",
 *   "date":"2008-09-15",
 *   "location":"http://sws.geonames.org/2635167/"
 * }
 *
 * Some of the properties of this object are then used to populate the pop-up
 * in Moodle which enables the piece of media to be selected.
 *
 * @package   repository_res
 * @copyright 2017, Elliot Smith <elliot.smith@bbc.co.uk>
 * @license   Apache v2 - http://www.apache.org/licenses/LICENSE-2.0
 */

// extract and decode querystring
if (!isset($_GET['media'])) {
    die('media parameter must be set');
}

$media = $_GET['media'];

$selected = json_decode($_GET['media']);

$uri = $selected->uri;
$label = $selected->label;

$thumbnail = '';
if (property_exists($selected, 'thumbnail')) {
    $thumbnail = $selected->thumbnail;
}

$date = '';
if (property_exists($selected, 'date')) {
    $date = $selected->date;
}

$html = <<<HTML
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <script type="text/javascript">
    window.onload = function() {
        var resource = {};
        resource.source = "$uri";
        resource.title = "$label";
        resource.thumbnail = "$thumbnail";
        resource.datecreated = "$date";
        resource.author = "";
        resource.license = "";
        parent.M.core_filepicker.select_file(resource);
    }
    </script>
</head>
<body></body>
</html>
HTML;

// Output the generated HTML
header('Content-Type: text/html; charset=utf-8');
echo $html;
