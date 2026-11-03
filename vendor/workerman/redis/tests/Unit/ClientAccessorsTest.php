<?php

use Workerman\Redis\Client;

/*
|--------------------------------------------------------------------------
| Client local accessors (phpredis-compat getters, no server)
|--------------------------------------------------------------------------
|
| These cover the real local accessor methods that replaced the broken
| @method stubs (getHost/getPort/getDbNum/getAuth/getTimeout/getReadTimeout/
| isConnected/getLastError/clearLastError/getPersistentID) plus the
| getMultiple() MGET alias. They derive their return value from stored client
| state only — no socket, no event loop, no server round-trip — so the
| in-process newInstanceWithoutConstructor() seam (see ClientCommandShapingTest)
| is sufficient: we set the backing properties via reflection and assert the
| getter output. getMultiple() is the one server command here; with
| $_connection === null process() is inert, so we assert the queued wire array.
*/

/**
 * Build a Client without running its constructor ($_connection stays null,
 * no Timer, no connect()).
 */
function accClient(): Client
{
    return (new ReflectionClass(Client::class))->newInstanceWithoutConstructor();
}

/**
 * Set a protected/private property on a Client via reflection.
 *
 * @param mixed $value
 */
function accSet(Client $client, string $name, $value): void
{
    $prop = (new ReflectionClass(Client::class))->getProperty($name);
    $prop->setAccessible(true);
    $prop->setValue($client, $value);
}

/**
 * Read a protected/private property off a Client via reflection.
 *
 * @return mixed
 */
function accGet(Client $client, string $name)
{
    $prop = (new ReflectionClass(Client::class))->getProperty($name);
    $prop->setAccessible(true);

    return $prop->getValue($client);
}

final class ClientAccessorsTest extends \Tests\TestCase
{
    // -----------------------------------------------------------------------
    // getHost / getPort  (parsed from $_address)
    // -----------------------------------------------------------------------

    public function test_gethost_parses_the_host_out_of_the_stored_address_url(): void
    {
        $client = accClient();
        accSet($client, '_address', 'redis://127.0.0.1:6379');

        $this->assertSame('127.0.0.1', $client->getHost());
    }

    public function test_gethost_returns_null_when_the_address_cannot_be_parsed_for_a_host(): void
    {
        $client = accClient();
        accSet($client, '_address', '');

        $this->assertNull($client->getHost());
    }

    public function test_getport_parses_the_port_out_of_the_stored_address_url(): void
    {
        $client = accClient();
        accSet($client, '_address', 'redis://127.0.0.1:6390');

        $this->assertSame(6390, $client->getPort());
    }

    public function test_getport_defaults_to_6379_when_the_address_carries_no_explicit_port(): void
    {
        $client = accClient();
        accSet($client, '_address', 'redis://127.0.0.1');

        $this->assertSame(6379, $client->getPort());
    }

    // -----------------------------------------------------------------------
    // getDbNum / getAuth  ($_db, $_auth)
    // -----------------------------------------------------------------------

    public function test_getdbnum_returns_the_locally_tracked_database_index_as_an_int(): void
    {
        $client = accClient();
        accSet($client, '_db', 4);

        $this->assertSame(4, $client->getDbNum());
        $this->assertIsInt($client->getDbNum());
    }

    public function test_getauth_returns_the_stored_string_credential(): void
    {
        $client = accClient();
        accSet($client, '_auth', 's3cret');

        $this->assertSame('s3cret', $client->getAuth());
    }

    public function test_getauth_returns_the_stored_acl_user_pass_array_credential(): void
    {
        $client = accClient();
        accSet($client, '_auth', ['alice', 's3cret']);

        $this->assertSame(['alice', 's3cret'], $client->getAuth());
    }

    public function test_getauth_returns_null_when_no_credential_was_set(): void
    {
        $client = accClient();
        accSet($client, '_auth', null);

        $this->assertNull($client->getAuth());
    }

    // -----------------------------------------------------------------------
    // getTimeout / getReadTimeout  (from $_options)
    // -----------------------------------------------------------------------

    public function test_gettimeout_returns_the_configured_connect_timeout_option(): void
    {
        $client = accClient();
        accSet($client, '_options', ['connect_timeout' => 7]);

        $this->assertSame(7, $client->getTimeout());
    }

    public function test_gettimeout_returns_null_when_connect_timeout_is_not_configured(): void
    {
        $client = accClient();
        accSet($client, '_options', []);

        $this->assertNull($client->getTimeout());
    }

    public function test_getreadtimeout_returns_the_configured_wait_timeout_option(): void
    {
        $client = accClient();
        accSet($client, '_options', ['wait_timeout' => 120]);

        $this->assertSame(120, $client->getReadTimeout());
    }

    public function test_getreadtimeout_returns_null_when_wait_timeout_is_not_configured(): void
    {
        $client = accClient();
        accSet($client, '_options', []);

        $this->assertNull($client->getReadTimeout());
    }

    // -----------------------------------------------------------------------
    // isConnected  ($_connection null vs an object)
    // -----------------------------------------------------------------------

    public function test_isconnected_returns_false_when_there_is_no_connection(): void
    {
        $client = accClient();
        accSet($client, '_connection', null);

        $this->assertFalse($client->isConnected());
    }

    public function test_isconnected_returns_true_only_when_the_connection_reports_established(): void
    {
        $client = accClient();

        // A tiny stand-in exposing getStatus(false); no real socket needed.
        $established = new class {
            public function getStatus($raw = true)
            {
                return 'ESTABLISHED';
            }
        };
        accSet($client, '_connection', $established);
        $this->assertTrue($client->isConnected());

        $connecting = new class {
            public function getStatus($raw = true)
            {
                return 'CONNECTING';
            }
        };
        accSet($client, '_connection', $connecting);
        $this->assertFalse($client->isConnected());
    }

    // -----------------------------------------------------------------------
    // getLastError / clearLastError  ($_error sentinel normalisation)
    // -----------------------------------------------------------------------

    public function test_getlasterror_returns_null_when_no_error_is_stored_the_empty_string_sentinel(): void
    {
        $client = accClient();
        accSet($client, '_error', '');

        $this->assertNull($client->getLastError());
    }

    public function test_getlasterror_returns_the_stored_error_string_when_one_is_set(): void
    {
        $client = accClient();
        accSet($client, '_error', 'ERR something went wrong');

        $this->assertSame('ERR something went wrong', $client->getLastError());
    }

    public function test_clearlasterror_wipes_the_stored_error_and_returns_true(): void
    {
        $client = accClient();
        accSet($client, '_error', 'ERR boom');

        $this->assertTrue($client->clearLastError());
        $this->assertSame('', accGet($client, '_error'));
        $this->assertNull($client->getLastError());
    }

    // -----------------------------------------------------------------------
    // getPersistentID  (always null for this async client)
    // -----------------------------------------------------------------------

    public function test_getpersistentid_is_always_null_no_persistent_connections(): void
    {
        $client = accClient();

        $this->assertNull($client->getPersistentID());
    }

    // -----------------------------------------------------------------------
    // getMultiple  (MGET alias — queues ['MGET', ...$keys])
    // -----------------------------------------------------------------------

    public function test_getmultiple_queues_an_mget_command_with_the_keys_in_order(): void
    {
        $client = accClient();
        $client->getMultiple(['k1', 'k2', 'k3']);

        $queue = accGet($client, '_queue');
        $this->assertCount(1, $queue);
        $this->assertSame(['MGET', 'k1', 'k2', 'k3'], $queue[0][0]);
        // No trailing callable supplied -> callback slot is null.
        $this->assertNull($queue[0][2] ?? null);
    }

    public function test_getmultiple_pops_a_trailing_callable_as_the_callback(): void
    {
        $client = accClient();
        $cb = function () {};
        $client->getMultiple(['k1', 'k2'], $cb);

        $queue = accGet($client, '_queue');
        $this->assertSame(['MGET', 'k1', 'k2'], $queue[0][0]);
        $this->assertSame($cb, $queue[0][2] ?? null);
    }
}
