<?php

namespace mindplay\agent;

use mindplay\agent\model\RequestEnvelope;
use mindplay\agent\model\ResponseEnvelope;

interface Client
{
    /**
     * @param RequestEnvelope $request
     *
     * @return ResponseEnvelope
     */
    public function sendRequest(RequestEnvelope $request);
}
