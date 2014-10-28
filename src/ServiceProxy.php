<?php

namespace mindplay\agent;

interface ServiceProxy
{
    /**
     * @param string
     * @return mixed
     */
    public function __get($name);

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value);

    /**
     * @param string $name
     * @param array $params
     */
    public function __call($name, $params);
}
