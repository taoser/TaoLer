<?php

final class ZScanTest extends \Tests\RedisTestCase
{
    public function test_zscan_returns_cursor_and_member_score_assoc(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:zscan:t1:zset';
            $redis->del($key);
            $pairs = ['m1' => 1, 'm2' => 2, 'm3' => 3, 'm4' => 4, 'm5' => 5];
            $remaining = count($pairs);
            foreach ($pairs as $member => $score) {
                $redis->zAdd($key, $score, $member, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $collected = [];
                        $types = [];
                        $loop = null;
                        $loop = function ($reply) use (&$loop, &$collected, &$types, $redis, $emit, $key) {
                            if (!is_array($reply) || !isset($reply['cursor'])) {
                                $emit(['error' => 'bad reply', 'reply' => $reply]);
                                return;
                            }
                            foreach ($reply['members'] as $m => $s) {
                                $collected[$m] = $s;
                                $types[$m] = gettype($s);
                            }
                            if ($reply['cursor'] === '0') {
                                $emit([
                                    'cursor_type' => gettype($reply['cursor']),
                                    'cursor_final' => $reply['cursor'],
                                    'members' => $collected,
                                    'score_types' => $types,
                                ]);
                                return;
                            }
                            $redis->zScan($key, $reply['cursor'], [], $loop);
                        };
                        $redis->zScan($key, '0', [], $loop);
                    }
                });
            }
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('string', $result['cursor_type']);
        $this->assertSame('0', $result['cursor_final']);
        ksort($result['members']);
        $this->assertSame([
            'm1' => '1',
            'm2' => '2',
            'm3' => '3',
            'm4' => '4',
            'm5' => '5',
        ], $result['members']);
        // Scores must stay as strings — float casts lose precision.
        foreach ($result['score_types'] as $type) {
            $this->assertSame('string', $type);
        }
    }

    public function test_zscan_with_match_filters_members_by_pattern(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:zscan:t2:zset';
            $redis->del($key);
            $pairs = ['a1' => 1, 'a2' => 2, 'b1' => 3];
            $remaining = count($pairs);
            foreach ($pairs as $member => $score) {
                $redis->zAdd($key, $score, $member, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $collected = [];
                        $loop = null;
                        $loop = function ($reply) use (&$loop, &$collected, $redis, $emit, $key) {
                            if (!is_array($reply) || !isset($reply['cursor'])) {
                                $emit(['error' => 'bad reply', 'reply' => $reply]);
                                return;
                            }
                            foreach ($reply['members'] as $m => $s) {
                                $collected[$m] = $s;
                            }
                            if ($reply['cursor'] === '0') {
                                $emit($collected);
                                return;
                            }
                            $redis->zScan($key, $reply['cursor'], ['MATCH' => 'a*'], $loop);
                        };
                        $redis->zScan($key, '0', ['MATCH' => 'a*'], $loop);
                    }
                });
            }
PHP
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('a1', $result);
        $this->assertArrayHasKey('a2', $result);
        $this->assertArrayNotHasKey('b1', $result);
    }

    public function test_zscan_with_count_respects_the_hint(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:zscan:t3:zset';
            $redis->del($key);
            $remaining = 30;
            for ($i = 1; $i <= 30; $i++) {
                $redis->zAdd($key, $i, 'm'.$i, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $redis->zScan($key, '0', ['COUNT' => 10], function ($reply) use ($emit) {
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
        // COUNT is only a hint. Dragonfly may return all 30 in one batch.
        // The meaningful assertion is that the call did not error.
        $this->assertGreaterThanOrEqual(1, $result['count']);
        $this->assertLessThanOrEqual(30, $result['count']);
    }

    public function test_zscanall_iterates_the_full_sorted_set(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:zscanall:t4:zset';
            $redis->del($key);
            $total = 150;
            $remaining = $total;
            for ($i = 1; $i <= $total; $i++) {
                $redis->zAdd($key, $i, 'm'.$i, function () use (&$remaining, $redis, $emit, $key, $total) {
                    $remaining--;
                    if ($remaining === 0) {
                        $redis->zScanAll($key, ['COUNT' => 25], function ($all) use ($emit) {
                            $emit([
                                'count'  => is_array($all) ? count($all) : -1,
                                'unique' => is_array($all) ? count(array_unique(array_keys($all))) : -1,
                                'sample_42' => is_array($all) ? ($all['m42'] ?? null) : null,
                                'sample_42_type' => is_array($all) && isset($all['m42']) ? gettype($all['m42']) : null,
                                'sample_99' => is_array($all) ? ($all['m99'] ?? null) : null,
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
        // Scores come back as bulk strings — not floats — to preserve precision.
        $this->assertSame('42', $result['sample_42']);
        $this->assertSame('string', $result['sample_42_type']);
        $this->assertSame('99', $result['sample_99']);
    }

    public function test_zscanall_honors_the_limit_option(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:zscanall:t5:zset';
            $redis->del($key);
            $total = 100;
            $remaining = $total;
            for ($i = 1; $i <= $total; $i++) {
                $redis->zAdd($key, $i, 'm'.$i, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $redis->zScanAll($key, ['COUNT' => 25, 'limit' => 30], function ($all) use ($emit) {
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

    public function test_zscan_on_a_missing_key_returns_empty_members(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:zscan:missing:xxxxxxxx:zset';
            $redis->del($key);
            $redis->zScan($key, '0', [], function ($reply) use ($emit) {
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

    public function test_zscan_with_malformed_cursor_passes_through_the_error(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:zscan:malformed:zset';
            $redis->del($key);
            $redis->zAdd($key, 1, 'm1', function () use ($redis, $emit, $key) {
                $redis->zScan($key, 'not-a-number', [], function ($reply) use ($emit) {
                    $emit([
                        'is_array'   => is_array($reply),
                        'is_bool'    => is_bool($reply),
                        'reply_type' => gettype($reply),
                    ]);
                });
            });
PHP
        );

        $this->assertIsArray($result);
        // The result must NOT be the normal ['cursor' => ..., 'members' => ...] shape.
        $this->assertSame(false, $result['is_array']);
    }

    public function test_zscan_preserves_score_precision_as_strings(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:zscan:t8:zset';
            $redis->del($key);
            // 1.5 has an exact binary representation, so Redis/Dragonfly returns it
            // verbatim. This proves the client doesn't reformat or float-cast the score.
            $redis->zAdd($key, 1.5, 'precise', function () use ($redis, $emit, $key) {
                $redis->zScan($key, '0', [], function ($reply) use ($emit) {
                    $emit([
                        'score'      => $reply['members']['precise'] ?? null,
                        'score_type' => isset($reply['members']['precise']) ? gettype($reply['members']['precise']) : null,
                    ]);
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('string', $result['score_type']);
        $this->assertSame('1.5', $result['score']);
    }
}
