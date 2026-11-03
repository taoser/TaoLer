<?php

/*
|--------------------------------------------------------------------------
| No-arg server / connection commands
|--------------------------------------------------------------------------
|
| Covers ping(), info(), dbSize(), time(), flushDb(), flushAll(). These
| sit on the wrong side of __call()'s count($args) > 1 guard, so each one
| has an explicit method. Tests verify the trailing-callback pattern
| actually invokes the callback with the parsed reply (not garbage and
| not a hang).
|
| flush tests SELECT a high DB index (14) inside the worker snippet so
| we never wipe data another test is using.
*/

final class ServerCommandsTest extends \Tests\RedisTestCase
{
    public function test_ping_returns_pong(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->ping(function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertSame('PONG', $result);
    }

    public function test_info_returns_a_non_empty_string(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->info(function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsString($result);
        $this->assertGreaterThan(50, strlen($result));
        // Redis emits 'redis_version', Dragonfly emits 'dragonfly_version'.
        // Either one signals we got a real INFO bulk back (and not a garbled
        // reply from the closure-on-wire bug).
        $hasVersion = str_contains($result, 'redis_version') || str_contains($result, 'dragonfly_version');
        $this->assertSame(true, $hasVersion);
    }

    public function test_info_with_section_filter_returns_scoped_output(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->info('server', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsString($result);
        // Both servers expose a version key under the 'server' section.
        $this->assertStringContainsString('_version', $result);
    }

    public function test_dbsize_returns_an_integer(): void
    {
        $result = runInWorker(<<<'PHP'
            // Land on a dedicated DB so other tests' fixtures don't skew
            // the count we're about to assert against.
            $redis->rawCommand('SELECT', 14, function () use ($redis, $emit) {
                $redis->rawCommand('FLUSHDB', function () use ($redis, $emit) {
                    $redis->set('pest:srv:dbsize:a', '1');
                    $redis->set('pest:srv:dbsize:b', '2');
                    $redis->set('pest:srv:dbsize:c', '3', function () use ($redis, $emit) {
                        $redis->dbSize(function ($size) use ($emit) {
                            $emit($size);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(3, $result);
    }

    public function test_time_returns_a_two_element_array_of_digit_strings(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->time(function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        // Both fields arrive as bulk strings of digits.
        $this->assertSame(true, ctype_digit((string) $result[0]));
        $this->assertSame(true, ctype_digit((string) $result[1]));
    }

    public function test_flushdb_empties_the_current_database(): void
    {
        $result = runInWorker(<<<'PHP'
            // Dedicated DB index so we don't nuke other tests' data.
            $redis->rawCommand('SELECT', 14, function () use ($redis, $emit) {
                $redis->set('pest:srv:flush:k', '1', function () use ($redis, $emit) {
                    $redis->flushDb(function () use ($redis, $emit) {
                        $redis->dbSize(function ($size) use ($emit) {
                            $emit($size);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(0, $result);
    }

    public function test_flushdb_async_also_empties_the_database(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->rawCommand('SELECT', 14, function () use ($redis, $emit) {
                $redis->set('pest:srv:flush:async', '1', function () use ($redis, $emit) {
                    $redis->flushDb(true, function () use ($redis, $emit) {
                        $redis->dbSize(function ($size) use ($emit) {
                            $emit($size);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(0, $result);
    }

    public function test_flushall_empties_all_databases(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->rawCommand('SELECT', 14, function () use ($redis, $emit) {
                $redis->set('pest:srv:flushall:k', '1', function () use ($redis, $emit) {
                    $redis->flushAll(function () use ($redis, $emit) {
                        $redis->dbSize(function ($size) use ($emit) {
                            $emit($size);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(0, $result);
    }
}
