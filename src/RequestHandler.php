<?php

namespace mindplay\agent;

use mindplay\agent\model\RequestEnvelope;
use mindplay\agent\model\ResponseEnvelope;

interface RequestHandler
{
    /**
     * @param RequestEnvelope $request
     *
     * @return ResponseEnvelope
     *
     * @throws RequestException
     */
    public function handleRequest(RequestEnvelope $request);
}
