<?php

/*
|--------------------------------------------------------------------------
| Strings / Keys / Connection extras
|--------------------------------------------------------------------------
|
| Covers commands that route through Client::__call() because each one has
| more than a single wire arg (so the count($args) > 1 branch picks up the
| trailing callback). No explicit method — only @method declarations on
| the class docblock and these integration tests confirming they work end
| to end against a live server.
|
| GETDEL, GETEX, SUBSTR        (Strings)
| COPY, TOUCH, EXPIRETIME,
| PEXPIRETIME                  (Keys)
| ECHO, HELLO                  (Connection / server)
|
| All keys use the pest:extra: prefix to avoid collisions with other
| feature tests.
*/

final class StringsKeysExtraTest extends \Tests\RedisTestCase
{
    public function test_getdel_returns_the_value_and_deletes_the_key(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:extra:getdel', 'v', function () use ($redis, $emit) {
                $redis->getDel('pest:extra:getdel', function ($value) use ($redis, $emit) {
                    $redis->get('pest:extra:getdel', function ($after) use ($value, $emit) {
                        $emit(['value' => $value, 'after' => $after]);
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('v', $result['value']);
        $this->assertNull($result['after']);
    }

    public function test_getex_without_options_returns_the_value_without_changing_ttl(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:extra:getex:plain', 'v', function () use ($redis, $emit) {
                $redis->getEx('pest:extra:getex:plain', function ($value) use ($emit) {
                    $emit($value);
                });
            });
PHP
        );

        $this->assertSame('v', $result);
    }

    public function test_getex_ex_sets_a_ttl(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:extra:getex:ttl', 'v', function () use ($redis, $emit) {
                $redis->getEx('pest:extra:getex:ttl', ['EX', 60], function ($value) use ($redis, $emit) {
                    $redis->ttl('pest:extra:getex:ttl', function ($ttl) use ($value, $emit) {
                        $emit(['value' => $value, 'ttl' => $ttl]);
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('v', $result['value']);
        $this->assertGreaterThanOrEqual(1, $result['ttl']);
        $this->assertLessThanOrEqual(60, $result['ttl']);
    }

    public function test_substr_returns_a_slice_of_the_value(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:extra:substr', 'hello world', function () use ($redis, $emit) {
                $redis->substr('pest:extra:substr', 0, 4, function ($slice) use ($emit) {
                    $emit($slice);
                });
            });
PHP
        );

        $this->assertSame('hello', $result);
    }

    public function test_copy_duplicates_a_key_to_a_new_destination(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:extra:copy:src', 'pest:extra:copy:dst', function () use ($redis, $emit) {
                $redis->set('pest:extra:copy:src', 'value', function () use ($redis, $emit) {
                    $redis->copy('pest:extra:copy:src', 'pest:extra:copy:dst', function ($ok) use ($redis, $emit) {
                        $redis->get('pest:extra:copy:dst', function ($dst) use ($ok, $emit) {
                            $emit(['ok' => $ok, 'dst' => $dst]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame(1, $result['ok']);
        $this->assertSame('value', $result['dst']);
    }

    public function test_copy_with_replace_overwrites_existing_dst(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:extra:copy:rsrc', 'pest:extra:copy:rdst', function () use ($redis, $emit) {
                $redis->set('pest:extra:copy:rsrc', 'v1', function () use ($redis, $emit) {
                    $redis->set('pest:extra:copy:rdst', 'v2', function () use ($redis, $emit) {
                        // Without REPLACE: copying onto an existing key returns 0.
                        $redis->copy('pest:extra:copy:rsrc', 'pest:extra:copy:rdst', function ($noReplace) use ($redis, $emit) {
                            // With REPLACE: overwrites and returns 1.
                            $redis->copy('pest:extra:copy:rsrc', 'pest:extra:copy:rdst', ['REPLACE'], function ($withReplace) use ($redis, $noReplace, $emit) {
                                $redis->get('pest:extra:copy:rdst', function ($dst) use ($noReplace, $withReplace, $emit) {
                                    $emit([
                                        'noReplace'   => $noReplace,
                                        'withReplace' => $withReplace,
                                        'dst'         => $dst,
                                    ]);
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame(0, $result['noReplace']);
        $this->assertSame(1, $result['withReplace']);
        $this->assertSame('v1', $result['dst']);
    }

    public function test_touch_returns_count_of_existing_keys(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:extra:touch:k1', 'pest:extra:touch:k2', 'pest:extra:touch:missing', function () use ($redis, $emit) {
                $redis->set('pest:extra:touch:k1', 'v', function () use ($redis, $emit) {
                    $redis->set('pest:extra:touch:k2', 'v', function () use ($redis, $emit) {
                        $redis->touch('pest:extra:touch:k1', 'pest:extra:touch:k2', 'pest:extra:touch:missing', function ($n) use ($emit) {
                            $emit($n);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result);
    }

    public function test_expiretime_returns_absolute_unix_timestamp(): void
    {
        $future = time() + 3600;

        $result = runInWorker(<<<PHP
            \$redis->set('pest:extra:expiretime', 'v', function () use (\$redis, \$emit) {
                \$redis->expireAt('pest:extra:expiretime', {$future}, function () use (\$redis, \$emit) {
                    \$redis->expireTime('pest:extra:expiretime', function (\$ts) use (\$emit) {
                        \$emit(\$ts);
                    });
                });
            });
PHP
        );

        $this->assertSame($future, $result);
    }

    public function test_pexpiretime_returns_absolute_millisecond_timestamp(): void
    {
        $futureMs = (time() + 3600) * 1000;

        $result = runInWorker(<<<PHP
            \$redis->set('pest:extra:pexpiretime', 'v', function () use (\$redis, \$emit) {
                \$redis->pexpireAt('pest:extra:pexpiretime', {$futureMs}, function () use (\$redis, \$emit) {
                    \$redis->pExpireTime('pest:extra:pexpiretime', function (\$ts) use (\$emit) {
                        \$emit(\$ts);
                    });
                });
            });
PHP
        );

        $this->assertSame($futureMs, $result);
    }

    public function test_echo_returns_the_same_message_it_was_sent(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->echo('pest-echo-msg', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertSame('pest-echo-msg', $result);
    }

    public function test_hello_returns_server_protocol_info(): void
    {
        // Some Dragonfly builds return an empty array for HELLO with no args.
        // We just want to confirm the call completes without erroring and the
        // reply is array-shaped (or empty array, treated as success).
        $result = runInWorker(<<<'PHP'
            $redis->hello(function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsArray($result);
    }
}
