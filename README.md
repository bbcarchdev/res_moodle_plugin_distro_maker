# RES Moodle plugin distro maker

[The Research and Education Space (RES)](http://res.space/) is a project
to provide educational users with access to public archives. The RES Moodle
plugin enables Moodle course administrators to add RES media resources from
those public archives to Moodle courses.

For the RES Moodle plugin to work, two components must be in place:

*   res_search_service

    This is a web service which proxies requests to
    [Acropolis](http://acropolis.org.uk), the RES RDF platform. It does the
    necessary work to retrieve full descriptions for media related to a
    search term, and provides a JSON representation of the important data. It
    also provides the UI for the Moodle plugin (using the
    [external file chooser](https://docs.moodle.org/dev/Repository_plugins_embedding_external_file_chooser)
    pattern).

*   moodle-repository_res

    This is the core part of plugin code which interfaces between Moodle and the
    res_search_service. It is installed into a Moodle environment using the
    [standard plugin installation mechanism](https://docs.moodle.org/33/en/Installing_plugins).

These two components can be installed on separate hosts. However, to make
installation simpler, this tool (the RES Moodle plugin distro maker)
combines the components into a single distributable Moodle plugin.
The resulting plugin runs the res_search_service inside the Moodle installation,
hosted under the same directory as the moodle-repository_res plugin code.

This is done by making a Moodle plugin folder layout from the moodle-repository_res
and res_search_service repositories. Then, some of the files in
res_search_service are replaced to fix routing inside the res_search_service
app, so that they correctly point at Moodle URLs (see `handlers/` directory).

Finally, a zip file is created. This is a standalone distributable Moodle plugin
(distro) which can be published on
[the Moodle plugin directory](http://moodle.org/plugins); it can be
downloaded and installed via the Moodle admin interface, the same way as any
other plugin.

Under normal circumstances, a Moodle administrator doesn't need to use the RES
Moodle plugin distro maker tool at all: they can just download a zip file from
the Moodle plugin registry.

## Building the distro

```
composer install
./vendor/bin/robo all
```

Once finished, `dist` contains the Moodle plugin and the RES search service,
configured in such a way that they can both be deployed to Moodle as
a single plugin. The `repository_res.zip` is a zipped version of the `dist`
directory.

## Author

[Elliot Smith](https://github.com/townxelliot) - elliot.smith@bbc.co.uk

## Licence

This project is licensed under the terms of the
[Apache License, version 2.0](http://www.apache.org/licenses/LICENSE-2.0)
(see LICENCE-APACHE.txt).

Copyright © 2017 BBC

The output zip file produced by this tool combines the moodle-repository_res
plugin (GPLv3 licence) with the res_search_service (Apache v2 licence). However,
they remain in separate directories and the zip file is a mere aggregation
of the two. Only the moodle-repository_res plugin interfaces with Moodle.
The res_search_service is accessed over its RESTful HTTP API and there are no
direct calls from moodle-repository_res to functions in the res_search_service
module.

The metadata file, thirdpartylibs.xml, produced as part of the output of this
tool and also in the zip file, lists the licences of all of the aggregated
libraries which provide the plugin functionality, as required by the Moodle
plugin directory.

