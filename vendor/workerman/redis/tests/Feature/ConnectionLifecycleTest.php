<?php

/*
|--------------------------------------------------------------------------
| Connection / lifecycle commands (Group 3.1)
|--------------------------------------------------------------------------
|
| Covers the connection-state verbs that aren't exercised elsewhere:
|   - auth()  failure path (no password configured on either backend)
|   - select() to a valid DB, and to an out-of-range DB index (error path)
|   - closeConnection() / close() lifecycle teardown
|
| Plus the HELLO handshake reply (Group 3.2): StringsKeysExtraTest only
| asserts hello() is array-shaped; here we pin the actual key/value map the
| server returns (server/version/proto/role) on both engines.
|
| Things already covered and intentionally NOT duplicated here:
|   - ping/info/dbSize/time/flushDb/flushAll  -> ServerCommandsTest
|   - save/lastSave/role/config/acl/...        -> ServerAdminTest
|   - bgSave                                    -> MiscTier9Test
|   - echo + bare hello() array shape           -> StringsKeysExtraTest
|   - quit + reconnect-after-quit              -> QuitTest
|
| onMessage delivers a -ERR reply to the callback as $result === false with
| the wire message available via $client->error(); these tests assert that
| error-delivery contract rather than the exact (engine-specific) wording.
*/

final class ConnectionLifecycleTest extends \Tests\RedisTestCase
{
    public function test_auth_surfaces_an_error_when_the_server_has_no_password_set(): void
    {
        // Engine divergence: with no password configured, stock Redis rejects
        // `AUTH <pass>` with -ERR, but Dragonfly accepts it and replies +OK.
        // We gate on the OBSERVED reply rather than the backend name so this file
        // is correct however it's invoked (dragonfly/redis/unset env). We do NOT
        // reconfigure either server.
        $result = runInWorker(<<<'PHP'
            $redis->auth('definitely-not-the-password', function ($reply, $client) use ($emit) {
                $emit([
                    'reply' => $reply,
                    'error' => $client->error(),
                ]);
            });
PHP
        );

        $this->assertIsArray($result);

        if ($result['reply'] !== false) {
            // Server accepted AUTH with no password set (Dragonfly behaviour) —
            // there is no error-delivery path to observe here.
            skipTest(
                '[' . currentBackend() . '] server accepted AUTH with no password set (reply '
                . var_export($result['reply'], true) . '); no -ERR rejection to assert'
            );
        }

        // Stock Redis answers `AUTH <pass>` (no requirepass) with an error reply.
        // Assert the client DELIVERS that error (reply === false + non-empty
        // error()) rather than hanging or treating it as success.
        $this->assertIsString($result['error']);
        $this->assertNotSame('', $result['error']);
        // Both engines mention AUTH/password in the no-password rejection; accept
        // either wording without pinning the exact phrasing.
        $msg = strtolower($result['error']);
        $mentionsAuth = str_contains($msg, 'auth') || str_contains($msg, 'password');
        $this->assertTrue($mentionsAuth);
    }

    public function test_auth_does_not_poison_auth_after_a_rejected_credential(): void
    {
        // The format closure in Client::auth() only records the credential on a
        // non-false reply. A rejected AUTH must leave the protected $_auth unset
        // so a later reconnect doesn't replay a bad credential. Verify via
        // reflection in-worker, after the AUTH callback has run. Gated on the
        // observed reply (see previous test): Dragonfly accepts AUTH with no
        // password set, so there is no rejection to observe there.
        $result = runInWorker(<<<'PHP'
            $redis->auth('still-not-the-password', function ($reply, $client) use ($emit) {
                $ref = new \ReflectionClass($client);
                $prop = $ref->getProperty('_auth');
                $prop->setAccessible(true);
                $emit([
                    'reply'   => $reply,
                    'authSet' => $prop->getValue($client),
                ]);
            });
PHP
        );

        $this->assertIsArray($result);

        if ($result['reply'] !== false) {
            skipTest(
                '[' . currentBackend() . '] server accepted AUTH with no password set; no rejected credential to observe'
            );
        }

        // _auth defaults to null/false and must stay falsy after a rejected AUTH.
        $this->assertFalse((bool) $result['authSet']);
    }

    public function test_select_switches_to_a_valid_database_and_records_it(): void
    {
        // SELECT 1 is valid on both Redis (16 DBs default) and Dragonfly. The
        // format closure records the new DB only on success, so we also confirm
        // the protected $_db tracker followed the switch.
        $result = runInWorker(<<<'PHP'
            $redis->select(1, function ($reply, $client) use ($emit) {
                $ref = new \ReflectionClass($client);
                $prop = $ref->getProperty('_db');
                $prop->setAccessible(true);
                $emit([
                    'reply' => $reply,
                    'db'    => $prop->getValue($client),
                ]);
            });
PHP
        );

        $this->assertIsArray($result);
        // SELECT replies +OK, which onMessage normalises to boolean true.
        $this->assertTrue($result['reply']);
        $this->assertSame(1, (int) $result['db']);
    }

    public function test_select_to_an_out_of_range_db_index_surfaces_an_error_and_leaves_db_unchanged(): void
    {
        // SELECT 9999 is out of range on BOTH engines (-ERR "DB index is out of
        // range"). The client must deliver reply === false with a non-empty
        // error(), and Client::select()'s format closure must NOT advance $_db (so
        // a reconnect won't replay a DB the server rejected).
        $result = runInWorker(<<<'PHP'
            // Land on DB 2 first so we have a known-good baseline to prove the
            // failed SELECT did not move us.
            $redis->select(2, function () use ($redis, $emit) {
                $redis->select(9999, function ($reply, $client) use ($emit) {
                    $ref = new \ReflectionClass($client);
                    $prop = $ref->getProperty('_db');
                    $prop->setAccessible(true);
                    $emit([
                        'reply' => $reply,
                        'error' => $client->error(),
                        'db'    => $prop->getValue($client),
                    ]);
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertFalse($result['reply']);
        $this->assertNotSame('', $result['error']);
        // _db must still reflect the last SUCCESSFUL select (2), not 9999.
        $this->assertSame(2, (int) $result['db']);
    }

    public function test_closeconnection_tears_down_the_socket_and_nulls_the_connection(): void
    {
        // closeConnection() detaches the AsyncTcpConnection handlers, closes the
        // socket, and nulls $_connection. Unlike quit(), it does NOT set
        // $_quitting. Issue a command first to prove the link was live, then close
        // and inspect the protected state via reflection.
        $result = runInWorker(<<<'PHP'
            $redis->ping(function ($reply, $client) use ($emit) {
                $client->closeConnection();
                \Workerman\Timer::add(0.3, function () use ($client, $reply, $emit) {
                    $ref = new \ReflectionClass($client);
                    $connProp = $ref->getProperty('_connection');
                    $connProp->setAccessible(true);
                    $emit([
                        'pong'       => $reply,
                        'connIsNull' => $connProp->getValue($client) === null,
                    ]);
                }, [], false);
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('PONG', $result['pong']);
        $this->assertTrue($result['connIsNull']);
    }

    public function test_hello_returns_a_handshake_map_with_server_version_proto_fields(): void
    {
        // HELLO replies with a RESP map that onMessage delivers as a flat
        // sequential array of [k, v, k, v, ...]. We fold it into an associative
        // map and assert the handshake fields both engines populate. Stock Redis
        // reports server=redis/version=8.x; Dragonfly reports server=redis with a
        // separate dragonfly_version key — so we pin the shared fields (server,
        // version, proto, mode, role) and tolerate the engine-specific extras.
        $result = runInWorker(<<<'PHP'
            $redis->hello(2, function ($reply) use ($emit) {
                // Fold flat [k,v,...] pairs into a map. Skip non-scalar values
                // (e.g. the RESP2 `modules` list nested under its key) so the
                // top-level handshake fields fold cleanly.
                $map = [];
                for ($i = 0; $i + 1 < count($reply); $i += 2) {
                    $k = $reply[$i];
                    $v = $reply[$i + 1];
                    if (is_string($k)) {
                        $map[$k] = $v;
                    }
                }
                $emit(['raw' => $reply, 'map' => $map]);
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertIsArray($result['raw']);
        $map = $result['map'];
        // Shared handshake fields present on both Redis and Dragonfly.
        $this->assertArrayHasKey('server', $map);
        $this->assertArrayHasKey('version', $map);
        $this->assertArrayHasKey('proto', $map);
        $this->assertArrayHasKey('role', $map);
        // Both engines identify the wire protocol family as "redis".
        $this->assertSame('redis', $map['server']);
        // We asked for RESP2; the server echoes the negotiated protocol version.
        $this->assertSame(2, (int) $map['proto']);
        // A standalone server reports the master role.
        $this->assertSame('master', $map['role']);
        // version is a non-empty bulk string (e.g. "8.8.0" / "7.4.0").
        $this->assertIsString($map['version']);
        $this->assertNotSame('', $map['version']);
    }

    public function test_close_empties_the_queue_and_releases_the_connection(): void
    {
        // close() calls closeConnection() and additionally clears $_queue and the
        // wait-timeout timer. After close() the queue must be empty and the
        // connection gone.
        $result = runInWorker(<<<'PHP'
            $redis->ping(function ($reply, $client) use ($emit) {
                $client->close();
                \Workerman\Timer::add(0.3, function () use ($client, $reply, $emit) {
                    $ref = new \ReflectionClass($client);
                    $connProp = $ref->getProperty('_connection');
                    $connProp->setAccessible(true);
                    $queueProp = $ref->getProperty('_queue');
                    $queueProp->setAccessible(true);
                    $emit([
                        'pong'       => $reply,
                        'connIsNull' => $connProp->getValue($client) === null,
                        'queueEmpty' => $queueProp->getValue($client) === [],
                    ]);
                }, [], false);
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('PONG', $result['pong']);
        $this->assertTrue($result['connIsNull']);
        $this->assertTrue($result['queueEmpty']);
    }
}
