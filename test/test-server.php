<?php

require __DIR__ . '/header.php';
require __DIR__ . '/src/TestServer.php';

$options = getopt('', array('port:'));

$server = TestServer::create(__DIR__, (int) $options['port']);

$server->run_forever();
