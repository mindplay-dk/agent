<?php

namespace mindplay\agent\server;

use mindplay\agent\model\RequestEnvelope;
use mindplay\agent\model\ResponseEnvelope;
use mindplay\agent\RequestHandler;

class HostController
{
    /** @var RequestHandler */
    private $handler;

    public function __construct(RequestHandler $handler)
    {
        $this->handler = $handler;
    }

    public function dispatch()
    {
        $request = unserialize($_POST[RequestEnvelope::KEY]);

        $response = $this->handler->handleRequest($request);

        echo serialize(array(ResponseEnvelope::KEY => $response));
    }
}
