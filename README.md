mindplay/agent
--------------

This library implements a service host and a service proxy with a matching client.

To host a service for remote usage by clients, implement a script like this:

```PHP
    // create your service:
    $service = new UserService();

    // create a host for the service, using a private key:
    $host = new ServiceHost($service, 'sup3rs3cr3t');

    // create a controller for the host:
    $controller = new HostController($host);

    // dispatch it:
    $controller->dispatch();
```

This script will now host the UserService for remote used by clients via http.

Let's say we deploy that as `http://your.host/user-service.php`.

To use this service from another site, implement a proxy/client like this:

```PHP
    // create an http client:
    $client = new HttpClient("http://your.host/user-service.php");

    // create a remove service proxy using the matching private key
    // and type-hint the proxy object as UserService for IDE support:

    /** @var UserService $service */
    $service = new RemoteServiceProxy($client, 'sup3rs3cr3t');
```

You can now program against `$service` as though it were an instance of `UserService`:

 * Setting and getting properties happens locally only - the proxy object simply
   stores and returns any property values applied to it.

 * When you invoke a method on the proxy object, the method-name and arguments
   will be serialized and forwarded to the remote service script, where the
   actual method will be invoked, and the return value from the call will
   be serialized and returned to the client.

 * The current state (properties) of the proxy object will be serialized and
   sent to the remote service, where they will be applied to it before the
   method call - and after the method call, any updated state will be serialized
   and returned to the client, which is updated to reflect the remote changes.

In effect, this lets you treat a remote service as though it were available
locally, with things like database queries etc. actually taking place on the
host, and the resulting data being made available through the proxy object.

This does not somehow just magically work with any service - it has some
limitations and caveats:

 * The service class and any models returned by it must be available in the
   client project, so that (for example) if a service method returns an object,
   this can be unserialized by the client.

 * Service state (not the service itself) and any model classes must be able
   to serialize and unserialize.

 * If the client makes multiple method calls during the lifetime of a proxy,
   it is effectively making separate round-trips to the host for each call,
   where the service is constructed, and has the current state (as held by
   the client) applied to it each time.

 * Accidental state in services or models can cause problems, since it will
   be transfered back and forth during requests, and probably should be
   avoided by design.

In effect, when designing services and models to be used in this way, you
should think of the client proxy as being the engine of state.
