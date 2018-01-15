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
    const res_search_service_repo = 'https://github.com/bbcarchdev/res_search_service';
    const moodle_repository_res_repo = 'https://github.com/bbcarchdev/moodle-repository_res';

    private function strReplace($file, $regex, $replacement)
    {
        if (!file_exists($file)) {
            throw new Exception("Cannot replace strings in $file as it doesn't exist");
        }

        $content = file_get_contents($file);
        $newContent = preg_replace($regex, $replacement, $content);
        file_put_contents($file, $newContent);
    }

    public function clean()
    {
        $this->taskFilesystemStack()->remove('repository_res.zip')->run();
        $this->taskDeleteDir('res_search_service')->run();
        $this->taskDeleteDir('moodle-repository_res')->run();
        $this->taskDeleteDir('dist')->run();
        $this->taskFilesystemStack()->mkdir('dist')->run();
    }

    public function clone()
    {
        if(file_exists('res_search_service')) {
            $this->taskGitStack()
                 ->dir('res_search_service')
                 ->pull()
                 ->run();
        } else {
            $this->taskGitStack()
                 ->cloneRepo(RoboFile::res_search_service_repo)
                 ->run();
        }

        if(file_exists('moodle-repository_res')) {
            $this->taskGitStack()
                 ->dir('moodle-repository_res')
                 ->pull()
                 ->run();
        } else {
            $this->taskGitStack()
                 ->cloneRepo(RoboFile::moodle_repository_res_repo)
                 ->run();
        }
    }

    public function deps()
    {
        $this->taskComposerUpdate()->dir('res_search_service')->run();
        $this->taskComposerInstall()->dir('res_search_service')->noDev()->run();
        $this->taskBowerInstall()->allowRoot()->dir('res_search_service')->run();
    }

    public function copyplugin()
    {
        $this->_copyDir('moodle-repository_res', 'dist');

        // fix the PLUGINSERVICE_URL to point at the Moodle server
        $this->strReplace(
            'dist/lib.php',
            "/getenv\('PLUGINSERVICE_URL'\)/",
            "'' . new moodle_url('/repository/res/service/')"
        );

        // remove .git and screenshots dirs from dist
        $this->taskDeleteDir('dist/.git')->run();
        $this->taskDeleteDir('dist/screenshots')->run();
    }

    public function copyservice()
    {
        $this->_copyDir('res_search_service/assets', 'dist/service/assets');
        $this->_copyDir('res_search_service/js', 'dist/service/js');
        $this->_copyDir('res_search_service/lib', 'dist/service/lib');
        $this->_copyDir('res_search_service/vendor', 'dist/service/vendor');
        $this->_copyDir(
            'res_search_service/bower_components',
            'dist/service/bower_components'
        );
        $this->_copyDir('res_search_service/views', 'dist/service/views');

        // add multiple handlers, one for each API endpoint, but all
        // of which require() a modified version of the index.php file
        // (here renamed to app.inc.php)
        $this->_copyDir('handlers', 'dist/service');

        // fix paths to JS, CSS, bower_components etc.
        $this->strReplace(
            'dist/service/views/minimal.html',
            '/\.\.\/bower_components/',
            'bower_components'
        );
        $this->strReplace(
            'dist/service/views/minimal.html',
            '/\.\.\/js/',
            'js'
        );

        // remove bbcarchdev/liblod-php files we don't need at runtime
        $this->taskDeleteDir('dist/service/vendor/bbcarchdev/liblod/.git')->run();
        $this->taskDeleteDir('dist/service/vendor/bbcarchdev/liblod/tests')->run();
        $this->taskDeleteDir('dist/service/vendor/bbcarchdev/liblod/tools')->run();

        // remove Pimple tests which cause lint error when passed through
        // Moodle's lint checks
        $this->taskDeleteDir('dist/service/vendor/pimple/pimple/ext/pimple/tests/')->run();
    }

    // remove .gitignore files from dist/
    public function removegitignore()
    {
        $dir_iterator = new RecursiveDirectoryIterator('dist');
        $iterator = new RecursiveIteratorIterator(
            $dir_iterator,
            RecursiveIteratorIterator::SELF_FIRST
        );

        $files_to_remove = [];

        foreach ($iterator as $file) {
            if ($file->getFilename() == '.gitignore')
            {
                $files_to_remove[] = $file->getPathname();
            }
        }

        $this->taskFilesystemStack()->remove($files_to_remove)->run();
    }

    // figure out which third party composer and bower libraries we're using and
    // make a Moodle thirdpartylibs.xml file with the results
    public function thirdparty()
    {
        $template = file_get_contents('thirdpartylibs.xml.tpl');
        $libraries = [];

        // composer libraries
        $composerLibraryDirs = glob('dist/service/vendor/*/*', GLOB_ONLYDIR);
        foreach ($composerLibraryDirs as $composerLibraryDir) {
            // ignore the composer directory
            if (preg_match('/composer/', $composerLibraryDir)) {
                continue;
            }

            $composerFile = $composerLibraryDir . '/composer.json';
            if (file_exists($composerFile)) {
                $obj = json_decode(file_get_contents($composerFile));
                $obj->location = preg_replace('/dist\//', '', $composerLibraryDir);
                $libraries[] = $obj;
            }
        }

        // bower components
        $bowerLibraryDirs = glob('dist/service/bower_components/*', GLOB_ONLYDIR);
        foreach ($bowerLibraryDirs as $bowerLibraryDir) {
            $bowerFile = $bowerLibraryDir . '/bower.json';
            if (file_exists($bowerFile)) {
                $obj = json_decode(file_get_contents($bowerFile));
                $obj->location = preg_replace('/dist\//', '', $bowerLibraryDir);
                $libraries[] = $obj;
            }
        }

        $engine = new Mustache_Engine();
        $out = $engine->render($template, ['libraries' => $libraries]);

        file_put_contents('dist/thirdpartylibs.xml', $out);
    }

    public function zip()
    {
        $this->taskPack('repository_res.zip')->addDir('res', 'dist')->run();
    }

    public function all()
    {
        $this->clone();
        $this->deps();
        $this->copyplugin();
        $this->copyservice();
        $this->removegitignore();
        $this->thirdparty();
        $this->zip();
    }
}
