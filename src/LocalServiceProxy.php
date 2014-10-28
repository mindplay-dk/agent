<?php

namespace mindplay\agent;

class LocalServiceProxy implements ServiceProxy
{
    /** @var object */
    private $_service;

    /**
     * @param object $service
     */
    public function __construct($service)
    {
        $this->_service = $service;
    }

    public function __get($name)
    {
        return $this->_service->$name;
    }

    public function __set($name, $value)
    {
        $this->_service->$name = $value;
    }

    public function __call($name, $params)
    {
        return call_user_func_array(array($this->_service, $name), $params);
    }
}
