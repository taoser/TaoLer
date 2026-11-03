<?php

use Workerman\Redis\Client;

/*
|--------------------------------------------------------------------------
| Method surface assertions
|--------------------------------------------------------------------------
|
| Pure reflection checks for explicit methods on Client whose live
| behaviour is either dangerous (SHUTDOWN tears the server down) or only
| meaningful against a specific server flavour (DIGEST is Dragonfly). The
| Feature/ suite exercises everything else end-to-end against the live
| server; this file just guards that the explicit-method declarations
| stay in place so __call() can't silently swallow them.
|
| Bound to Tests\TestCase (not RedisTestCase) so this file passes even
| when no Redis is reachable.
*/

final class MethodSurfaceTest extends \Tests\TestCase
{
    public function test_shutdown_is_declared_with_the_expected_signature(): void
    {
        // hasMethod() goes through ReflectionClass instead of method_exists(),
        // which PHPStan would prove always-true (since Client is loaded at
        // analyse time). Reflection-based checks stay dynamic from PHPStan's
        // point of view but still catch a deletion or rename.
        $ref = new ReflectionClass(Client::class);
        $this->assertTrue($ref->hasMethod('shutdown'));

        $method = $ref->getMethod('shutdown');
        $params = $method->getParameters();
        $this->assertCount(2, $params);
        $this->assertSame('mode', $params[0]->getName());
        $this->assertTrue($params[0]->isOptional());
        $this->assertSame('SAVE', $params[0]->getDefaultValue());
        $this->assertSame('cb', $params[1]->getName());
        $this->assertTrue($params[1]->isOptional());
    }

    public function test_server_admin_dispatcher_methods_exist_on_client(): void
    {
        $ref = new ReflectionClass(Client::class);
        foreach (['config', 'acl', 'slowLog', 'memory', 'command', 'cluster'] as $name) {
            $this->assertTrue($ref->hasMethod($name));
        }
    }

    public function test_no_arg_server_admin_methods_exist_on_client(): void
    {
        $ref = new ReflectionClass(Client::class);
        foreach (['lastSave', 'save', 'role', 'digest', 'shutdown'] as $name) {
            $this->assertTrue($ref->hasMethod($name));
        }
    }

    public function test_monitor_is_declared_with_the_expected_signature(): void
    {
        // MONITOR is long-lived and would tear up a shared server's throughput, so
        // its streaming behaviour is exercised in Feature/MonitorTest rather than
        // here. Guard the surface so __call() can't silently swallow it.
        $ref = new ReflectionClass(Client::class);
        $this->assertTrue($ref->hasMethod('monitor'));

        $method = $ref->getMethod('monitor');
        $this->assertTrue($method->isPublic());
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('cb', $params[0]->getName());
        // $cb is required — a future default would mean monitor() with no callback
        // silently locks the connection and discards the stream.
        $this->assertFalse($params[0]->isOptional());
    }

    public function test_unsubscribe_family_methods_exist_on_client(): void
    {
        // These bypass the subscribe-lock by writing straight to the socket, so
        // their behaviour is exercised live in Feature/UnsubscribeTest. Here we
        // just guard that the explicit declarations stay in place — __call() can't
        // reach them (the SUBSCRIBE lock would swallow a queued unsubscribe).
        $ref = new ReflectionClass(Client::class);
        foreach (['unsubscribe', 'pUnsubscribe', 'sUnsubscribe'] as $name) {
            $this->assertTrue($ref->hasMethod($name));
            $this->assertTrue($ref->getMethod($name)->isPublic());
        }
    }
}
