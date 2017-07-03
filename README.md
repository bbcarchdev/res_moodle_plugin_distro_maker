# RES Moodle plugin distro maker

This tool creates a Moodle plugin folder layout from the RES Moodle plugin
and the RES search service.

The output folder structure it creates can then be checked into git and used
as the basis for plugin installers.

## Running it

```
composer install
./vendor/bin/robo all
```

Once finished, `dist` contains the Moodle plugin + the RES search service,
configured in such a way that they can both be deployed to Moodle as
a single plugin.
