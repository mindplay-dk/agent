<?php

namespace mindplay\agent\client;

use mindplay\agent\Client;
use mindplay\agent\ClientException;
use mindplay\agent\model\RequestEnvelope;
use mindplay\agent\model\ResponseEnvelope;

class HttpClient implements Client
{
    /** @var string absolute URL of service host endpoint */
    private $_host;

    /**
     * @param string $host absolute URL of service host endpoint
     */
    public function __construct($host)
    {
        $this->_host = $host;
    }

    /**
     * @param RequestEnvelope $request
     *
     * @return ResponseEnvelope
     */
    public function sendRequest(RequestEnvelope $request)
    {
        $content = $this->sendHttpRequest(
            $this->_host,
            array(
                RequestEnvelope::KEY => serialize($request),
            )
        );

        $data = unserialize($content);

        if (!is_array($data) || !isset($data[ResponseEnvelope::KEY])) {
            throw new ClientException("invalid response");
        }

        /** @var ResponseEnvelope $response */
        $response = $data[ResponseEnvelope::KEY];

        return $response;
    }

    /**
     * @param string $url
     * @param array  $data
     *
     * @return string
     */
    private function sendHttpRequest($url, $data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $result = curl_exec($ch);

        $errno = curl_errno($ch);

        curl_close($ch);

        if ($errno) {
            throw new ClientException(curl_strerror($errno), $errno);
        }

        return $result;
    }
}
