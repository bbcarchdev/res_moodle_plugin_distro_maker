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
                 ->cloneRepo('ssh://git@bitbucket.org/townxelliot/res_search_service.git')
                 ->run();
        }

        if(file_exists('moodle-repository_res'))
        {
            $this->taskGitStack()->dir('moodle-repository_res')->pull()->run();
        }
        else
        {
            $this->taskGitStack()
                 ->cloneRepo('ssh://git@bitbucket.org/townxelliot/moodle-repository_res.git')
                 ->run();
        }
    }

    public function compose()
    {
        $this->taskComposerInstall()->dir('res_search_service')->noDev()->run();
    }

    public function copyplugin()
    {
        $this->_copyDir('moodle-repository_res', 'dist');
    }

    public function copyservice()
    {
        $this->_copyDir('res_search_service/js', 'dist/service/js');
        $this->_copyDir('res_search_service/lib', 'dist/service/lib');
        $this->_copyDir('res_search_service/vendor', 'dist/service/vendor');
        $this->_copyDir('res_search_service/views', 'dist/service/views');

        $this->taskFilesystemStack()
             ->copy('res_search_service/index.php', 'dist/service/index.php')
             ->run();
    }

    public function replace()
    {
        $this->strReplace('dist/service/index.php', "/app->get\('\/api/", "app->get('/repository/res/service");
        $this->strReplace('dist/service/index.php', "/app->get\('\/'/", "app->get('/repository/res/service/'");
        $this->strReplace('dist/lib.php', "/getenv\('PLUGINSERVICE_URL'\)/", "new moodle_url('/repository/res/service/')");
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

    public function removegit()
    {
        $this->taskDeleteDir('dist/service/vendor/res/liblod/.git')->run();
    }

    public function all()
    {
        $this->clone();
        $this->compose();
        $this->copyplugin();
        $this->copyservice();
        $this->replace();
        $this->thirdparty();
        $this->removegit();
    }
}
