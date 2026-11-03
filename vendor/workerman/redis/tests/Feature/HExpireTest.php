<?php

/*
|--------------------------------------------------------------------------
| Tier 9 — HEXPIRE family
|--------------------------------------------------------------------------
|
| The HEXPIRE family applies TTLs to individual hash fields. Wire form for
| every member is `HCMD key [seconds|millis|ts] [NX|XX|GT|LT] FIELDS
| numfields field [field...]`. Replies are arrays of per-field integers.
|
| Dragonfly currently only ships HEXPIRE and HTTL — HPERSIST, HEXPIREAT,
| HEXPIRETIME, HPEXPIRE, HPTTL all reply -ERR unknown command. The unknown
| ones are tested via $redis->error() and skipped rather than asserting a
| fixed reply, so the suite stays green as Dragonfly catches up.
|
| Each test uses a unique pest:hexpire:tN: prefix so concurrent runs don't
| collide.
*/

final class HExpireTest extends \Tests\RedisTestCase
{
    public function test_hexpire_applies_a_ttl_to_a_single_field(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hexpire:t1:hash';
            $redis->del($key, function () use ($redis, $emit, $key) {
                $redis->hSet($key, 'f1', 'v1', function () use ($redis, $emit, $key) {
                    // HEXPIRE key seconds FIELDS numfields field [field...]
                    $redis->hExpire($key, 100, 'FIELDS', 1, 'f1', function ($reply) use ($redis, $emit, $key) {
                        $redis->del($key, function () use ($emit, $reply) {
                            $emit($reply);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame(1, $result[0]);
    }

    public function test_httl_reads_back_the_field_ttl_set_by_hexpire(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hexpire:t2:hash';
            $redis->del($key, function () use ($redis, $emit, $key) {
                $redis->hSet($key, 'f1', 'v1', function () use ($redis, $emit, $key) {
                    $redis->hExpire($key, 200, 'FIELDS', 1, 'f1', function () use ($redis, $emit, $key) {
                        $redis->hTtl($key, 'FIELDS', 1, 'f1', function ($reply) use ($redis, $emit, $key) {
                            $redis->del($key, function () use ($emit, $reply) {
                                $emit($reply);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        // hTtl returns an integer per requested field. On Dragonfly the
        // remaining TTL is reported in seconds — just confirm it's a sane
        // positive integer (not -1 = no ttl, not -2 = no such field).
        $this->assertIsInt($result[0]);
        $this->assertGreaterThan(0, $result[0]);
        $this->assertLessThanOrEqual(200, $result[0]);
    }

    public function test_hexpire_reports_2_for_fields_that_do_not_exist(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hexpire:t3:hash';
            $redis->del($key, function () use ($redis, $emit, $key) {
                $redis->hSet($key, 'f1', 'v1', function () use ($redis, $emit, $key) {
                    // ghost is not set — Dragonfly should return -2 for it.
                    $redis->hExpire($key, 100, 'FIELDS', 2, 'f1', 'ghost', function ($reply) use ($redis, $emit, $key) {
                        $redis->del($key, function () use ($emit, $reply) {
                            $emit($reply);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]);
        $this->assertSame(-2, $result[1]);
    }

    public function test_hpersist_removes_the_ttl_tolerates_dragonfly_builds_without_the_command(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hexpire:t4:hash';
            $redis->del($key, function () use ($redis, $emit, $key) {
                $redis->hSet($key, 'f1', 'v1', function () use ($redis, $emit, $key) {
                    $redis->hExpire($key, 100, 'FIELDS', 1, 'f1', function () use ($redis, $emit, $key) {
                        $redis->hPersist($key, 'FIELDS', 1, 'f1', function ($reply) use ($redis, $emit, $key) {
                            $err = $redis->error();
                            $redis->del($key, function () use ($emit, $reply, $err) {
                                $emit(['reply' => $reply, 'err' => $err]);
                            });
                        });
                    });
                });
            });
PHP
        );

        // Dragonfly currently doesn't implement HPERSIST — accept the unknown-
        // command error path as well as the per-field integer-array reply that
        // future builds will return. Either way confirms we wired the verb
        // through __call() correctly.
        if (\is_string($result['err']) && $result['err'] !== '') {
            $this->assertStringContainsString('unknown command', $result['err']);
            return;
        }
        $this->assertIsArray($result['reply']);
        $this->assertSame(1, $result['reply'][0]);
    }

    public function test_hexpireat_sets_an_absolute_deadline_tolerates_dragonfly_builds_without_the_command(): void
    {
        $deadline = time() + 200;

        $result = runInWorker(<<<PHP
            \$key = 'pest:hexpire:t5:hash';
            \$redis->del(\$key, function () use (\$redis, \$emit, \$key) {
                \$redis->hSet(\$key, 'f1', 'v1', function () use (\$redis, \$emit, \$key) {
                    \$redis->hExpireAt(\$key, {$deadline}, 'FIELDS', 1, 'f1', function (\$reply) use (\$redis, \$emit, \$key) {
                        \$err = \$redis->error();
                        \$redis->del(\$key, function () use (\$emit, \$reply, \$err) {
                            \$emit(['reply' => \$reply, 'err' => \$err]);
                        });
                    });
                });
            });
PHP
        );

        if (\is_string($result['err']) && $result['err'] !== '') {
            $this->assertStringContainsString('unknown command', $result['err']);
            return;
        }
        $this->assertIsArray($result['reply']);
        $this->assertSame(1, $result['reply'][0]);
    }

    public function test_hexpiretime_returns_the_absolute_deadline_tolerates_dragonfly_builds_without_the_command(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hexpire:t6:hash';
            $redis->del($key, function () use ($redis, $emit, $key) {
                $redis->hSet($key, 'f1', 'v1', function () use ($redis, $emit, $key) {
                    $redis->hExpire($key, 200, 'FIELDS', 1, 'f1', function () use ($redis, $emit, $key) {
                        $redis->hExpireTime($key, 'FIELDS', 1, 'f1', function ($reply) use ($redis, $emit, $key) {
                            $err = $redis->error();
                            $redis->del($key, function () use ($emit, $reply, $err) {
                                $emit(['reply' => $reply, 'err' => $err]);
                            });
                        });
                    });
                });
            });
PHP
        );

        if (\is_string($result['err']) && $result['err'] !== '') {
            $this->assertStringContainsString('unknown command', $result['err']);
            return;
        }
        $this->assertIsArray($result['reply']);
        // The reported deadline is in unix-seconds and well in the future.
        $this->assertIsInt($result['reply'][0]);
        $this->assertGreaterThan(\time(), $result['reply'][0]);
    }
}
