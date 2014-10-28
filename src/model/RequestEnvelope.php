<?php

namespace mindplay\agent\model;

use mindplay\agent\model\Envelope;

class RequestEnvelope extends Envelope
{
    /** @type string */
    const KEY = 'request';

    /** @var array */
    public $state;

    /** @var string */
    public $method_name;

    /** @var array */
    public $params;

    /**
     * @param array  $state
     * @param string $method_name
     * @param array  $params
     * @param string $salt
     */
    public function __construct($state, $method_name, $params, $salt)
    {
        $this->state = $state;
        $this->method_name = $method_name;
        $this->params = $params;

        $this->_salt($salt);
    }

    /**
     * @return int
     */
    protected function getVersion()
    {
        return 1;
    }
}
