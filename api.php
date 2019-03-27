<?php

define('ROOT', dirname(__DIR__ . '/..'));
require ROOT . '/app/App.php';
App::load();

require ROOT . '/app/Components/Api/Api.php';
