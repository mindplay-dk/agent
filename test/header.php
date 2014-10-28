<?php

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';
$autoloader->addPsr4('mindplay\agent\\', __DIR__ . '/src');

require __DIR__ . '/src/UserService.php';
