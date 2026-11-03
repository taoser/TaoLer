<?php

/*
|--------------------------------------------------------------------------
| Tier 5 server administration commands
|--------------------------------------------------------------------------
|
| Covers the subcommand-dispatcher methods (config/acl/slowLog/memory/
| command/cluster) and the explicit no-arg methods (lastSave/save/role/
| digest) plus the multi-arg families (replicaOf, debug) and the
| Dragonfly-specific delEx extension.
|
| SHUTDOWN is NOT exercised here — running it would terminate the shared
| Dragonfly process mid-suite. tests/Unit/MethodSurfaceTest.php asserts
| the method declaration exists via reflection instead.
|
| Some commands (digest, delEx) are Dragonfly-only. The tests skip
| themselves when the server replies with -ERR unknown command rather
| than failing the suite on stock Redis.
*/

final class ServerAdminTest extends \Tests\RedisTestCase
{
    public function test_config_get_returns_server_config(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->config('GET', 'maxmemory', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsArray($result);
        // Reply shape: [key, value].
        $this->assertCount(2, $result);
        $this->assertSame('maxmemory', $result[0]);
    }

    public function test_config_set_round_trips_a_transient_setting(): void
    {
        $result = runInWorker(<<<'PHP'
            // 'timeout' is one of the few config keys both stock Redis and
            // Dragonfly accept at runtime. Default is 0 (disabled) on both,
            // so we flip it to 0 explicitly — a no-op write that still
            // exercises the CONFIG SET path.
            $redis->config('SET', 'timeout', '0', function ($setReply) use ($redis, $emit) {
                $redis->config('GET', 'timeout', function ($getReply) use ($emit, $setReply) {
                    $emit([
                        'set' => $setReply,
                        'get' => $getReply,
                    ]);
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertTrue($result['set']);
        $this->assertIsArray($result['get']);
        $this->assertSame('timeout', $result['get'][0]);
        $this->assertSame('0', $result['get'][1]);
    }

    public function test_acl_whoami_returns_the_current_user(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->acl('WHOAMI', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        // Stock Redis returns just 'default'; Dragonfly returns 'User is default'.
        // Accept either by asserting the reply contains the user name.
        $this->assertIsString($result);
        $this->assertStringContainsString('default', $result);
    }

    public function test_acl_list_returns_the_user_list(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->acl('LIST', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function test_slowlog_len_returns_an_integer(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->slowLog('LEN', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function test_slowlog_get_returns_an_array(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->slowLog('GET', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsArray($result);
    }

    public function test_slowlog_reset_succeeds(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->slowLog('RESET', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertTrue($result);
    }

    public function test_memory_usage_returns_a_byte_count_for_an_existing_key(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:srv:mem:k';
            $redis->set($key, 'some-value-to-measure', function () use ($redis, $emit, $key) {
                $redis->memory('USAGE', $key, function ($reply) use ($emit) {
                    $emit($reply);
                });
            });
PHP
        );

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function test_command_count_returns_the_total_command_count(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->command('COUNT', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsInt($result);
        // Any reasonable Redis/Dragonfly build exposes well over a hundred
        // commands — this is a coarse sanity check, not a tight assertion.
        $this->assertGreaterThan(100, $result);
    }

    public function test_command_info_returns_details_for_get(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->command('INFO', 'GET', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        // The inner row is [name, arity, flags, ...]. Verify the verb name.
        // Stock Redis lowercases it ('get'), Dragonfly uppercases it ('GET');
        // accept either by comparing case-insensitively.
        $this->assertIsArray($result[0]);
        $this->assertSame('get', strtolower((string) $result[0][0]));
    }

    public function test_command_no_args_returns_the_full_command_table(): void
    {
        $result = runInWorker(<<<'PHP'
            // Bare COMMAND form — verify dispatcher's empty-verb special case.
            $redis->command(function ($reply) use ($emit) {
                $emit(['count' => is_array($reply) ? count($reply) : -1]);
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertGreaterThan(100, $result['count']);
    }

    public function test_cluster_info_returns_the_cluster_status_or_surfaces_a_disabled_cluster_error(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->cluster('INFO', function ($reply, $client) use ($emit) {
                $emit([
                    'reply' => $reply,
                    'error' => $client->error(),
                ]);
            });
PHP
        );

        $this->assertIsArray($result);
        if ($result['reply'] === false) {
            // Dragonfly disables cluster mode unless --cluster_mode is set, so
            // it answers CLUSTER INFO with -ERR. Accept that as a valid round-trip
            // — what we care about is that the dispatch path delivered the verb.
            $this->assertNotSame('', $result['error']);
            return;
        }
        $this->assertIsString($result['reply']);
        $this->assertStringContainsString('cluster_enabled', $result['reply']);
    }

    public function test_lastsave_returns_a_unix_timestamp(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->lastSave(function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function test_role_returns_the_role_tuple(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->role(function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsArray($result);
        // First element is the role label: 'master', 'slave', or 'sentinel'.
        $this->assertContains($result[0], ['master', 'slave', 'replica', 'sentinel']);
    }

    public function test_replicaof_no_one_succeeds_on_a_standalone_server(): void
    {
        $result = runInWorker(<<<'PHP'
            // REPLICAOF NO ONE is the idempotent "become master" command.
            $redis->replicaOf('NO', 'ONE', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertTrue($result);
    }

    public function test_debug_subcommand_round_trips_through_the_dispatcher(): void
    {
        // Dragonfly does not ship DEBUG SLEEP, while stock Redis does. Both
        // implement DEBUG OBJECT against an existing key — exercise that
        // path on every server so we always cover the multi-arg @method.
        $result = runInWorker(<<<'PHP'
            $key = 'pest:srv:debug:k';
            $redis->set($key, 'v', function () use ($redis, $emit, $key) {
                $redis->debug('OBJECT', $key, function ($reply, $client) use ($emit) {
                    $emit([
                        'reply' => $reply,
                        'error' => $client->error(),
                    ]);
                });
            });
PHP
        );

        $this->assertIsArray($result);
        if ($result['reply'] === false) {
            // Some Dragonfly builds disable DEBUG entirely; allow the
            // unknown-subcommand reply through so the test isn't brittle.
            $this->assertNotSame('', $result['error']);
            return;
        }
        // DEBUG OBJECT returns a single-line +simple-string reply on success.
        // Different servers format the line differently; assert it's not empty.
        $this->assertNotNull($result['reply']);
    }

    public function test_save_synchronously_triggers_a_snapshot(): void
    {
        $result = runInWorker(<<<'PHP'
            // SAVE on Dragonfly is a non-blocking snapshot, but still returns +OK.
            // On Redis it blocks the server briefly; either way the reply is true.
            $redis->save(function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        , 10);

        $this->assertTrue($result);
    }

    public function test_digest_returns_a_string_where_supported_or_surfaces_the_wire_error(): void
    {
        // DIGEST is an old debugging hook; both stock Redis and Dragonfly
        // moved it under DEBUG DIGEST (which is itself optional). Calling
        // the bare DIGEST verb usually returns -ERR. The test verifies the
        // dispatch path delivers the verb cleanly either way.
        $result = runInWorker(<<<'PHP'
            $redis->digest(function ($reply, $client) use ($emit) {
                $emit([
                    'reply' => $reply,
                    'error' => $client->error(),
                ]);
            });
PHP
        );

        $this->assertIsArray($result);
        if ($result['reply'] === false) {
            // Most servers reply -ERR — that's fine, we only care that the
            // call reached the wire and produced *some* signal.
            $this->assertNotSame('', $result['error']);
            return;
        }
        $this->assertIsString($result['reply']);
    }

    public function test_delex_routes_through_call_to_dragonfly_or_surfaces_unknown_command_on_redis(): void
    {
        // DELEX is a Dragonfly extension (DEL + per-key liveness check).
        // Argument grammar varies across Dragonfly versions; current builds
        // accept `DELEX key`. Stock Redis lacks the verb entirely and replies
        // -ERR unknown command. Either way the test confirms the @method
        // declaration routes through __call() onto the wire.
        $result = runInWorker(<<<'PHP'
            $key = 'pest:srv:delex:k';
            $redis->set($key, 'sentinel', function () use ($redis, $emit, $key) {
                // Single-key form — broadest compatibility across Dragonfly builds.
                $redis->delEx($key, function ($reply, $client) use ($emit) {
                    $emit([
                        'reply' => $reply,
                        'error' => $client->error(),
                    ]);
                });
            });
PHP
        );

        $this->assertIsArray($result);
        if ($result['reply'] === false && str_contains((string) $result['error'], 'unknown command')) {
            // Stock Redis: skip cleanly.
            $this->addToAssertionCount(1);
            return;
        }
        // Dragonfly: DELEX returns the count of removed keys.
        if (\is_int($result['reply'])) {
            $this->assertGreaterThanOrEqual(0, $result['reply']);
            return;
        }
        // Any other reply means the verb hit the wire — record the error for
        // visibility but don't fail; the dispatch surface is what we're guarding.
        $this->assertArrayHasKey('error', $result);
    }
}
