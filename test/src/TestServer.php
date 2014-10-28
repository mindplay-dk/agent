<?php

class TestServer extends HTTPServer
{
    /** @var string */
    private $_path;

    /**
     * @param string $path
     * @param int $port
     *
     * @return self
     */
    public static function create($path, $port)
    {
        $server = new self(array('addr' => '127.0.0.1', 'port' => $port));

        $server->_path = $path;

        return $server;
    }

    /**
     * @param HTTPRequest $request
     *
     * @return HTTPResponse
     */
    public function route_request($request)
    {
        $path = $this->_path . $request->uri;

        if (preg_match('#\.php$#', $request->uri)) {
            return $this->get_php_response($request, $path);
        }  else {
            return $this->get_static_response($request, $path);
        }
    }
}
