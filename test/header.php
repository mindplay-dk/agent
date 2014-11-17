<?php

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';

$autoloader->addClassMap(array(
    'UserService' => __DIR__ . '/src/UserService.php',
    'TestServer' => __DIR__ . '/src/TestServer.php',
    'BackgroundTask' => __DIR__ . '/src/BackgroundTask.php',
));
