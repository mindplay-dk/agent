<?php

use mindplay\agent\server\HostController;
use mindplay\agent\server\ServiceHost;

require __DIR__ . '/header.php';

$service = new UserService();

$handler = new ServiceHost($service, 'abc123');

$controller = new HostController($handler);

$controller->dispatch();
