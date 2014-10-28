<?php

namespace mindplay\agent\server;

use mindplay\agent\model\RequestEnvelope;
use mindplay\agent\model\ResponseEnvelope;
use mindplay\agent\RequestException;
use mindplay\agent\RequestHandler;
use ReflectionClass;

class ServiceHost implements RequestHandler
{
    /** @var object */
    private $_service;

    /** @var string private service key */
    private $_key;

    /**
     * @param object $service
     * @param string $key
     */
    public function __construct($service, $key)
    {
        $this->_service = $service;
        $this->_key = $key;
    }

    /**
     * @param RequestEnvelope $request
     *
     * @return ResponseEnvelope
     *
     * @throws RequestException
     */
    public function handleRequest(RequestEnvelope $request)
    {
        if (! $request->verify($this->_key)) {
            throw new RequestException("invalid request");
        }

        foreach ($request->state as $name => $value) {
            $this->_service->$name = $value;
        }

        /** @var array $previous_state service state before method call */
        $previous_state = $this->getServiceState();

        $result = call_user_func_array(array($this->_service, $request->method_name), $request->params);

        /** @var array $previous_state service state after method call */
        $current_state = $this->getServiceState();

        /** @var array $updated_state updated service state (properties updated during method call) */
        $updated_state = array_diff_assoc($current_state, $previous_state);

        $response = new ResponseEnvelope($updated_state, $result, $this->_key);

        return $response;
    }

    /**
     * @return array service state (property values)
     */
    private function getServiceState()
    {
        $class = new ReflectionClass(get_class($this->_service));

        $state = array();

        foreach ($class->getProperties() as $prop) {
            $prop->setAccessible(true);

            $state[$prop->name] = $prop->getValue($this->_service);
        }

        return $state;
    }
}
