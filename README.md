# RES Moodle plugin distro maker

A RES Moodle plugin setup is usually composed of two pieces:

*   res_search_service

    This is a web service which proxies requests to
    [Acropolis](http://acropolis.org.uk), the RES RDF platform. It does the
    necessary work to retrieve full descriptions for media related to a
    search term, and provides a JSON representation of the important data. It
    also provides the UI for the Moodle plugin (using the
    [external file chooser](https://docs.moodle.org/dev/Repository_plugins_embedding_external_file_chooser)
    pattern).

*   moodle-repository_res

    This is the core part of the code which interfaces between Moodle and the
    res_search_service.

The usual arrangement for using the RES Moodle plugin is as follows:

* Install moodle-repository_res into Moodle as a plugin.
* Run res_search_service on a separate web host.
* Configure the plugin to send its requests to res_search_service.

RES Moodle plugin distro maker simplifies this arrangement so that the Moodle
plugin and the res_search_service run from the same folder on the Moodle host.
This removes the need for separate hosting for res_search_service.

The tool does this by creating a Moodle plugin folder layout from the RES
Moodle plugin and the RES search service repositories. Some of the files in
res_search_service are then patched to fix routing inside the res_search_service
app, so that they correctly point at Moodle URLs.

Finally, a zip file is created. This is a standalone distributable Moodle plugin
(distro) which can be published on
[the Moodle plugin registry](http://moodle.org/plugins); it can be
downloaded and installed via the Moodle admin interface, the same way as any
other plugin.

Under normal circumstances, an end user doesn't need to use RES Moodle plugin
distro maker at all: they just download a zip file from the Moodle plugin
registry. We merely open sourced the distro maker for the curious.

## Building the distro

```
composer install
./vendor/bin/robo all
```

Once finished, `dist` contains the Moodle plugin + the RES search service,
configured in such a way that they can both be deployed to Moodle as
a single plugin. The `repository_res.zip` is a zipped version of the `dist`
directory.

Note that the repo contains checked-in versions of the `dist/` and
`repository_res.zip` files. Building the distro will overwrite these files.

## Author

[Elliot Smith](https://github.com/townxelliot)

## Licence

Copyright © 2017 BBC

res_moodle_plugin_distro_maker is licensed under the terms of the Apache
License, Version 2.0 (see LICENCE-APACHE.txt).
