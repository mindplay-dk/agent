<?php

use mindplay\agent\model\RequestEnvelope;
use mindplay\agent\LocalServiceProxy;
use mindplay\agent\RemoteServiceProxy;
use mindplay\agent\client\HttpClient;
use mindplay\agent\model\ResponseEnvelope;
use mindplay\agent\server\ServiceHost;
use mindplay\agent\ServiceProxy;

require __DIR__ . '/header.php';

require __DIR__ . '/src/BackgroundTask.php';

test(
    'UserService fixture behavior',
    function () {
        /** @var UserService $service */
        $service = new UserService();

        testUserService($service);
    }
);

test(
    'Local ServiceProxy behavior',
    function () {
        /** @var UserService|ServiceProxy $service */
        $service = new LocalServiceProxy(new UserService());

        testUserService($service);
    }
);

/**
 * @param UserService $service
 */
function testUserService($service)
{
    $KEY = 'abc123';

    $service->key = $KEY;

    eq($service->key, $KEY, 'can get service state');
    eq($service->login('foo', 'bar'), true, 'can execute service method');
    eq($service->num_logins, 1, 'can get modified service state');
}

test(
    'Request transport envelope works as expected',
    function () {
        $SALT = 'abc123';

        $STATE = array(
            'foo' => 123,
            'bar' => 'xyz',
            'baz' => true,
        );

        $METHOD_NAME = 'the_method';

        $PARAMS = array(
            'a' => 456,
            'b' => 789,
        );

        $envelope = new RequestEnvelope($STATE, $METHOD_NAME, $PARAMS, $SALT);

        ok(is_float($envelope->timestamp), 'timestamp applied');

        ok($envelope->verify($SALT) === true, 'valid salt verified');
        ok($envelope->verify('wrong') === false, 'wrong salt rejected');

        $serialized = serialize($envelope);

        /** @var \mindplay\agent\model\RequestEnvelope $unserialized */
        $unserialized = unserialize($serialized);

        ok($unserialized->verify($SALT) === true, 'valid salt verified after serialize/unserialize');
        ok($unserialized->verify('wrong') === false, 'wrong salt rejected after serialize/unserialize');

        eq($unserialized->timestamp, $envelope->timestamp, 'timestamp survives serialize/unserialize');
        eq($unserialized->state, $envelope->state, 'state survives serialize/unserialize');
        eq($unserialized->params, $envelope->params, 'params survive serialize/unserialize');
        eq($unserialized->method_name, $envelope->method_name, 'method name survives serialize/unserialize');
    }
);

test(
    'Response transport envelope works as expected',
    function () {
        $SALT = 'abc123';

        $STATE = array(
            'foo' => 123,
            'bar' => 'xyz',
            'baz' => true,
        );

        $RESULT = 'the_result';

        $envelope = new ResponseEnvelope($STATE, $RESULT, $SALT);

        ok(is_float($envelope->timestamp), 'timestamp applied');

        ok($envelope->verify($SALT) === true, 'valid salt verified');
        ok($envelope->verify('wrong') === false, 'wrong salt rejected');

        $serialized = serialize($envelope);

        /** @var \mindplay\agent\model\ResponseEnvelope $unserialized */
        $unserialized = unserialize($serialized);

        ok($unserialized->verify($SALT) === true, 'valid salt verified after serialize/unserialize');
        ok($unserialized->verify('wrong') === false, 'wrong salt rejected after serialize/unserialize');

        eq($unserialized->state, $envelope->state, 'state survives serialize/unserialize');
        eq($unserialized->result, $envelope->result, 'state survives serialize/unserialize');
    }
);

test(
    'Can host a service',
    function () {
        $service = new UserService();

        $host = new ServiceHost($service, 'abc123');

        $SALT ='abc123';

        $request = new RequestEnvelope(
            array(
                'key' => 'abc123',
            ),
            'login',
            array(
                'bob',
                'p@ssword',
            ),
            $SALT
        );

        $response = $host->handleRequest($request);

        ok($response->verify($SALT), 'response envelope is valid');
        eq($service->num_logins, 1, 'service method was executed');
        eq($response->state, array('num_logins' => 1), 'updated state was returned');
        eq($response->result, true, 'return value was returned');
    }
);

test(
    'Can perform remote service request',
    function () {
        $HOST = '127.0.0.1';
        $PORT = 8000;
        $PATH = 'host.php';

        $server = new BackgroundTask(__DIR__ . "/test-server.php --port={$PORT}");

        /** @var UserService|ServiceProxy $service */
        $service = new RemoteServiceProxy(new HttpClient("http://{$HOST}:{$PORT}/{$PATH}"), 'abc123');

        testUserService($service);
    }
);

exit(status());

// https://gist.github.com/mindplay-dk/4260582

/**
 * @param string   $name     test description
 * @param callable $function test implementation
 */
function test($name, $function)
{
    echo "\n=== $name ===\n\n";

    try {
        call_user_func($function);
    } catch (Exception $e) {
        ok(false, "UNEXPECTED EXCEPTION", $e);
    }
}

/**
 * @param bool   $result result of assertion
 * @param string $why    description of assertion
 * @param mixed  $value  optional value (displays on failure)
 */
function ok($result, $why = null, $value = null)
{
    if ($result === true) {
        echo "- PASS: " . ($why === null ? 'OK' : $why) . ($value === null ? '' : ' (' . format($value) . ')') . "\n";
    } else {
        echo "# FAIL: " . ($why === null ? 'ERROR' : $why) . ($value === null ? '' : ' - ' . format($value, true)) . "\n";
        status(false);
    }
}

/**
 * @param mixed  $value    value
 * @param mixed  $expected expected value
 * @param string $why      description of assertion
 */
function eq($value, $expected, $why = null)
{
    $result = $value === $expected;

    $info = $result
        ? format($value)
        : "expected: " . format($expected, true) . ", got: " . format($value, true);

    ok($result, ($why === null ? $info : "$why ($info)"));
}

/**
 * @param string   $exception_type Exception type name
 * @param string   $why            description of assertion
 * @param callable $function       function expected to throw
 */
function expect($exception_type, $why, $function)
{
    try {
        call_user_func($function);
    } catch (Exception $e) {
        if ($e instanceof $exception_type) {
            ok(true, $why, $e);
            return;
        } else {
            $actual_type = get_class($e);
            ok(false, "$why (expected $exception_type but $actual_type was thrown)");
            return;
        }
    }

    ok(false, "$why (expected exception $exception_type was NOT thrown)");
}

/**
 * @param mixed $value
 * @param bool  $verbose
 *
 * @return string
 */
function format($value, $verbose = false)
{
    if ($value instanceof Exception) {
        return get_class($value)
        . ($verbose ? ": \"" . $value->getMessage() . "\"" : '');
    }

    if (! $verbose && is_array($value)) {
        return 'array[' . count($value) . ']';
    }

    if (is_bool($value)) {
        return $value ? 'TRUE' : 'FALSE';
    }

    if (is_object($value) && !$verbose) {
        return get_class($value);
    }

    return print_r($value, true);
}

/**
 * @param bool|null $status test status
 *
 * @return int number of failures
 */
function status($status = null)
{
    static $failures = 0;

    if ($status === false) {
        $failures += 1;
    }

    return $failures;
}
