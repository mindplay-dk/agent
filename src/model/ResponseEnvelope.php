<?php

namespace mindplay\agent\model;

use mindplay\agent\model\Envelope;

class ResponseEnvelope extends Envelope
{
    /** @type string */
    const KEY = 'response';

    /** @var array updated state (properties updated during a request) */
    public $state;

    /** @var mixed */
    public $result;

    /**
     * @param array  $state
     * @param mixed  $result
     * @param string $salt
     */
    public function __construct($state, $result, $salt)
    {
        $this->state = $state;
        $this->result = $result;

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
