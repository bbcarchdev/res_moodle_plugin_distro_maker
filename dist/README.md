# RES Moodle plugin

## Description

This plugin enables users to find media items indexed by the RES platform and insert them into Moodle.

RES is an open data platform built by the BBC. The platform indexes and organises the digital collections of libraries, museums, broadcasters and galleries to make their content more discoverable, accessible and usable to those in UK education and research. Images, TV and radio programmes, documents and text from world class organisations such as The British Museum, British Library, The National Archives, Europeana, Wellcome Trust and the BBC are all being indexed by RES. The majority of the media indexed by RES is free to use for educational purposes.

## How it works

Once installed, the RES Moodle plugin is available as a repository plugin, via the "Add an activity or resource" dialog, under the "Resources/URL" heading.

The search interface enables simple search and visualisation of the [RES API](http://acropolis.org.uk/), showing descriptions of media items and thumbnails where available.

Selecting a media item triggers a pop-up which can be used to insert the item's URL into Moodle. The URL of the item may point to a full web page (denoted as *Playable media* in the plugin), an embeddable image, video or audio item (denoted as *Embeddable media*), or a web page (denoted as *Web pages*).

Note that some playable media or web pages may prompt the user for authentication credentials or require agreement to terms and conditions before media are shown. By contrast, embeddable media should be openly accessible.

## Technical details

The plugin code is available at [moodle-repository_res](???repo).

This code connects to a [res_search_service](???repo) instance. res_search_service is a piece of middleware which runs as a standalone web service with HTML UI. It converts RDF from the RES platform to JSON, and does the logic to find RES resources with associated media.

In the Moodle context, the res_search_service UI acts as an [external file chooser](https://docs.moodle.org/dev/Repository_plugins_embedding_external_file_chooser): a user can search for topics with related media, choose a topic, then select a media item. The URL of the item is then incorporated into a piece of Moodle content.

res_search_service uses [liblod-php](???repo) to communicate with the RES API. liblod-php is a standalone PHP library for Linked Open Data (NB it isn't specific to RES).

[res_moodle_plugin_distro_maker](???repo) is a set of scripts which pull together moodle-respository_res, res_search_service and liblod-php, as well as their dependencies, into a single distributable zip file. This can be installed into Moodle as a plugin in the usual way.

Alternatively, res_search_service can run on a dedicated server and the Moodle plugin configured to talk to it remotely. res_search_service can even be run on its own as a simple UI for accessing the RES API.

Finally, [res_moodle_stack](???repo) provides a [Docker](http://www.docker.com/) configuration for testing the plugin inside a Moodle instance. This runs Moodle and the plugin distribution (made by res_moodle_plugin_distro_maker) on Apache and MySQL.

## Contributing

???

## Author

Elliot Smith - elliot.smith@bbc.co.uk

## Licence

This project is licensed under the terms of the [Apache License, version 2.0](http://www.apache.org/licenses/LICENSE-2.0).

Copyright © 2017 BBC

Because inclusion in the Moodle plugin registry requires *explicit* GPLv3 licensing, despite the Apache License, version 2.0 being [compatible]( https://www.gnu.org/licenses/license-list.en.html#GPLCompatibleLicenses) with the GPLv3, this project is *also* licensed under the terms of the [GNU General Public License (GPL) version 3](https://www.gnu.org/licenses/gpl.html).

If you are planning to distribute modified versions of this project, you may choose to release them according to the terms of either licence (you must state clearly in your release which you have chosen), or to keep both in place as we have here, which is generally the simpler option.
