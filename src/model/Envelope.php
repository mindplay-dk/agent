<?php

namespace mindplay\agent\model;

abstract class Envelope
{
    /** @var float */
    public $timestamp;

    /** @var int */
    private $_version;

    /** @var string */
    private $_checksum;

    /** @return int */
    abstract protected function getVersion();

    /**
     * @param string $salt
     * @return bool true on checksum match, otherwise false
     */
    public function verify($salt)
    {
        return $this->_checksum === $this->hash($salt);
    }

    /**
     * Apply timestamp, store version number, and salt the envelope.
     *
     * @param $salt
     */
    protected function _salt($salt)
    {
        $this->timestamp = microtime(true);

        $this->_version = $this->getVersion();

        $this->_checksum = $this->hash($salt);
    }

    /**
     * @param string $salt
     *
     * @return string
     */
    protected function hash($salt)
    {
        $checksum = $this->_checksum;

        $this->_checksum = null;

        $hash = sha1($salt . $this->_version . serialize($this));

        $this->_checksum = $checksum;

        return $hash;
    }
}
