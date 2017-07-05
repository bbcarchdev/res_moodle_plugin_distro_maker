<?php
/*
 * Copyright 2017 BBC
 *
 * Author: Elliot Smith <elliot.smith@bbc.co.uk>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use \Robo\Tasks as RoboTasks;

class RoboFile extends RoboTasks
{
    private function strReplace($file, $regex, $replacement)
    {
        $content = file_get_contents($file);
        $newContent = preg_replace($regex, $replacement, $content);
        file_put_contents($file, $newContent);
    }

    public function clean()
    {
        $this->taskDeleteDir('res_search_service')->run();
        $this->taskDeleteDir('moodle-repository_res')->run();
        $this->taskDeleteDir('dist')->run();
        $this->taskFilesystemStack()->mkdir('dist')->run();
    }

    public function clone()
    {
        if(file_exists('res_search_service'))
        {
            $this->taskGitStack()->dir('res_search_service')->pull()->run();
        }
        else
        {
            $this->taskGitStack()
                 ->cloneRepo('https://townxelliot@bitbucket.org/townxelliot/res_search_service.git')
                 ->run();
        }

        if(file_exists('moodle-repository_res'))
        {
            $this->taskGitStack()->dir('moodle-repository_res')->pull()->run();
        }
        else
        {
            $this->taskGitStack()
                 ->cloneRepo('https://townxelliot@bitbucket.org/townxelliot/moodle-repository_res.git')
                 ->run();
        }
    }

    public function deps()
    {
        $this->taskComposerInstall()->dir('res_search_service')->noDev()->run();
        $this->taskBowerInstall()->dir('res_search_service')->run();
    }

    public function copyplugin()
    {
        $this->_copyDir('moodle-repository_res', 'dist');

        // fix the PLUGINSERVICE_URL to point at the Moodle server
        $this->strReplace('dist/lib.php', "/getenv\('PLUGINSERVICE_URL'\)/", "'' . new moodle_url('/repository/res/service/')");
    }

    public function copyservice()
    {
        $this->_copyDir('res_search_service/js', 'dist/service/js');
        $this->_copyDir('res_search_service/lib', 'dist/service/lib');
        $this->_copyDir('res_search_service/vendor', 'dist/service/vendor');
        $this->_copyDir('res_search_service/bower_components', 'dist/service/bower_components');
        $this->_copyDir('res_search_service/views', 'dist/service/views');

        // add multiple handlers, one for each API endpoint, but all
        // of which require() the old (renamed) index.php file
        $this->_copyDir('handlers', 'dist/service');

        // copy the old index.php (single page app) script to a new location
        // to be used as a require()'d script
        $this->taskFilesystemStack()
             ->remove('dist/service/app.inc.php')
             ->copy('res_search_service/index.php', 'dist/service/app.inc.php')
             ->run();

        // comment out routes in app.inc.php
        $this->strReplace('dist/service/app.inc.php', '/\$app->get\(/', '//');

        // add a new route which uses a variable to determine the handler;
        // this variable is set in the new handler scripts
        $this->strReplace('dist/service/app.inc.php', '/\$app->run/', '\$app->get(\'/\', \$handler);' . "\n" . '\$app->run');

        // copy a custom capabilities.json file to the service root directory,
        // to direct all requests for services to the individual php scripts
        $this->taskFilesystemStack()
             ->copy('capabilities.json', 'dist/service/capabilities.json')
             ->run();

        // fix paths to JS, CSS, bower_components etc.
        $this->strReplace('dist/service/views/ui.html', '/\.\.\/bower_components/', 'bower_components');
        $this->strReplace('dist/service/views/ui.html', '/\.\.\/js/', 'js');

        // remove files we don't need at runtime
        $this->taskDeleteDir('dist/service/vendor/res/liblod/.git')->run();
        $this->taskDeleteDir('dist/service/vendor/res/liblod/tests')->run();
        $this->taskDeleteDir('dist/service/vendor/res/liblod/tools')->run();
    }

    public function thirdparty()
    {
        $template = file_get_contents('thirdpartylibs.xml.tpl');
        $libraries = [];

        $libraryDirs = glob('dist/service/vendor/*/*', GLOB_ONLYDIR);
        foreach($libraryDirs as $libraryDir)
        {
            if(preg_match('/composer/', $libraryDir))
            {
                continue;
            }

            $composerFile = $libraryDir . '/composer.json';
            if(file_exists($composerFile))
            {
                $obj = json_decode(file_get_contents($composerFile));
                $obj->location = preg_replace('/dist\//', '', $libraryDir);
                $libraries[] = $obj;
            }
        }

        $engine = new Mustache_Engine();
        $out = $engine->render($template, ['libraries' => $libraries]);

        file_put_contents('dist/thirdpartylibs.xml', $out);
    }

    public function zip()
    {
        $this->taskPack('repository_res.zip')
             ->addDir('res', 'dist')
             ->run();
    }

    public function all()
    {
        $this->clone();
        $this->deps();
        $this->copyplugin();
        $this->copyservice();
        $this->thirdparty();
        $this->zip();
    }
}
