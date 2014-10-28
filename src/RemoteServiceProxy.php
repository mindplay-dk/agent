<?php

namespace mindplay\agent;

use mindplay\agent\model\RequestEnvelope;

class RemoteServiceProxy implements ServiceProxy
{
    /** @var Client */
    private $_client;

    /** @var string */
    private $_key;

    /** @var array */
    private $_state = array();

    /**
     * @param Client $client
     * @param string $key
     */
    public function __construct(Client $client, $key)
    {
        $this->_client = $client;
        $this->_key = $key;
    }

    public function __get($name)
    {
        return $this->_state[$name];
    }

    public function __set($name, $value)
    {
        $this->_state[$name] = $value;
    }

    public function __call($name, $params)
    {
        $request = new RequestEnvelope($this->_state, $name, $params, $this->_key);

        $response = $this->_client->sendRequest($request);

        if (!$response->verify($this->_key)) {
            throw new ClientException("invalid response envelope");
        }

        foreach ($response->state as $name => $value) {
            $this->_state[$name] = $value;
        }

        return $response->result;
    }
}
