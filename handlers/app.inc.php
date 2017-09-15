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

$paths = array(
  "minimal" => "/repository/res/service/index.php",
  "search" => "/repository/res/service/search.php",
  "proxy" => "/repository/res/service/proxy.php",
  "audiences" => "/repository/res/service/audiences.php"
);

// start the app
require_once(__DIR__ . '/vendor/autoload.php');

use \Slim\App as SlimApp;
use \res\libres\RESClient;
use \res\libres\Controller;

// get Acropolis URL from env; if not set, RESClient sets a default
$acropolisUrl = getenv('ACROPOLIS_URL');
$client = new RESClient($acropolisUrl);

$app = new SlimApp();
$container = $app->getContainer();

$container['Controller'] = function($container) use($client, $paths) {
    return new Controller($client, $paths);
};

// $handler is set by the handler script which was actually called
$app->get('/', $handler);
$app->run();
