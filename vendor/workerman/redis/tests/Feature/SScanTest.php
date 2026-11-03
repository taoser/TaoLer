<?php

final class SScanTest extends \Tests\RedisTestCase
{
    public function test_sscan_returns_cursor_and_members_list(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:sscan:t1:set';
            $redis->del($key);
            $remaining = 5;
            foreach (['m1', 'm2', 'm3', 'm4', 'm5'] as $m) {
                $redis->sAdd($key, $m, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $collected = [];
                        $loop = null;
                        $loop = function ($reply) use (&$loop, &$collected, $redis, $emit, $key) {
                            if (!is_array($reply) || !isset($reply['cursor'])) {
                                $emit(['error' => 'bad reply', 'reply' => $reply]);
                                return;
                            }
                            foreach ($reply['members'] as $m) {
                                $collected[] = $m;
                            }
                            if ($reply['cursor'] === '0') {
                                $emit([
                                    'cursor_type'  => gettype($reply['cursor']),
                                    'cursor_final' => $reply['cursor'],
                                    'members'      => array_values(array_unique($collected)),
                                ]);
                                return;
                            }
                            $redis->sScan($key, $reply['cursor'], [], $loop);
                        };
                        $redis->sScan($key, '0', [], $loop);
                    }
                });
            }
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('string', $result['cursor_type']);
        $this->assertSame('0', $result['cursor_final']);
        sort($result['members']);
        $this->assertSame(['m1', 'm2', 'm3', 'm4', 'm5'], $result['members']);
    }

    public function test_sscan_with_match_filters_members(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:sscan:t2:set';
            $redis->del($key);
            $remaining = 3;
            foreach (['a1', 'a2', 'b1'] as $m) {
                $redis->sAdd($key, $m, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $collected = [];
                        $loop = null;
                        $loop = function ($reply) use (&$loop, &$collected, $redis, $emit, $key) {
                            if (!is_array($reply) || !isset($reply['cursor'])) {
                                $emit(['error' => 'bad reply', 'reply' => $reply]);
                                return;
                            }
                            foreach ($reply['members'] as $m) {
                                $collected[$m] = true;
                            }
                            if ($reply['cursor'] === '0') {
                                $emit(array_keys($collected));
                                return;
                            }
                            $redis->sScan($key, $reply['cursor'], ['MATCH' => 'a*'], $loop);
                        };
                        $redis->sScan($key, '0', ['MATCH' => 'a*'], $loop);
                    }
                });
            }
PHP
        );

        $this->assertIsArray($result);
        sort($result);
        $this->assertSame(['a1', 'a2'], $result);
        $this->assertNotContains('b1', $result);
    }

    public function test_sscan_with_count_respects_the_hint(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:sscan:t3:set';
            $redis->del($key);
            $remaining = 30;
            for ($i = 1; $i <= 30; $i++) {
                $redis->sAdd($key, 'm'.$i, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $redis->sScan($key, '0', ['COUNT' => 10], function ($reply) use ($emit) {
                            $emit([
                                'cursor' => $reply['cursor'] ?? null,
                                'count'  => is_array($reply['members'] ?? null) ? count($reply['members']) : -1,
                            ]);
                        });
                    }
                });
            }
PHP
        );

        $this->assertIsArray($result);
        // COUNT is purely a hint — Dragonfly may return all 30 in one batch.
        // The meaningful assertion is that the call did not error.
        $this->assertGreaterThanOrEqual(1, $result['count']);
        $this->assertLessThanOrEqual(30, $result['count']);
    }

    public function test_sscanall_iterates_the_full_set(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:sscanall:t4:set';
            $redis->del($key);
            $total = 150;
            $remaining = $total;
            for ($i = 1; $i <= $total; $i++) {
                $redis->sAdd($key, 'm'.$i, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $redis->sScanAll($key, ['COUNT' => 25], function ($all) use ($emit) {
                            $emit([
                                'count'  => is_array($all) ? count($all) : -1,
                                'unique' => is_array($all) ? count(array_unique($all)) : -1,
                            ]);
                        });
                    }
                });
            }
PHP
        , 10);

        $this->assertIsArray($result);
        $this->assertSame(150, $result['count']);
        $this->assertSame(150, $result['unique']);
    }

    public function test_sscanall_honors_the_limit_option(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:sscanall:t5:set';
            $redis->del($key);
            $total = 100;
            $remaining = $total;
            for ($i = 1; $i <= $total; $i++) {
                $redis->sAdd($key, 'm'.$i, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $redis->sScanAll($key, ['COUNT' => 25, 'limit' => 30], function ($all) use ($emit) {
                            $emit(['count' => is_array($all) ? count($all) : -1]);
                        });
                    }
                });
            }
PHP
        , 10);

        $this->assertIsArray($result);
        // limit caps each batch's contribution; the final count may exceed `limit`
        // by up to one COUNT batch because Redis returns whole batches at a time.
        $this->assertGreaterThanOrEqual(30, $result['count']);
        $this->assertLessThanOrEqual(55, $result['count']);
    }

    public function test_sscan_on_missing_key_returns_empty_members(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:sscan:missing:xxxxxxxx:set';
            $redis->del($key);
            $redis->sScan($key, '0', [], function ($reply) use ($emit) {
                $emit([
                    'cursor'      => $reply['cursor'] ?? null,
                    'cursor_type' => isset($reply['cursor']) ? gettype($reply['cursor']) : null,
                    'members'     => $reply['members'] ?? null,
                ]);
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('0', $result['cursor']);
        $this->assertSame('string', $result['cursor_type']);
        $this->assertSame([], $result['members']);
    }

    public function test_sscan_with_malformed_cursor_receives_false(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:sscan:malformed:set';
            $redis->del($key);
            $redis->sAdd($key, 'm', function () use ($redis, $emit, $key) {
                $redis->sScan($key, 'not-a-number', [], function ($reply) use ($emit) {
                    $emit([
                        'is_array'   => is_array($reply),
                        'is_bool'    => is_bool($reply),
                        'reply'      => $reply,
                        'reply_type' => gettype($reply),
                    ]);
                });
            });
PHP
        );

        $this->assertIsArray($result);
        // Redis/Dragonfly replies with an error string; the format callback passes
        // non-array results through unchanged so the caller gets `false`.
        $this->assertSame(false, $result['is_array']);
        $this->assertSame(true, $result['is_bool']);
        $this->assertSame(false, $result['reply']);
    }
}
