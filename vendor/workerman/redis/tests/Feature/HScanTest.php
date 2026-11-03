<?php

final class HScanTest extends \Tests\RedisTestCase
{
    public function test_hscan_returns_cursor_and_assoc_fields(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hscan:t1:hash';
            $redis->del($key);
            $remaining = 5;
            foreach (['f1' => 'v1', 'f2' => 'v2', 'f3' => 'v3', 'f4' => 'v4', 'f5' => 'v5'] as $f => $v) {
                $redis->hSet($key, $f, $v, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $collected = [];
                        $loop = null;
                        $loop = function ($reply) use (&$loop, &$collected, $redis, $emit, $key) {
                            if (!is_array($reply) || !isset($reply['cursor'])) {
                                $emit(['error' => 'bad reply', 'reply' => $reply]);
                                return;
                            }
                            foreach ($reply['fields'] as $f => $v) {
                                $collected[$f] = $v;
                            }
                            if ($reply['cursor'] === '0') {
                                $emit([
                                    'cursor_type' => gettype($reply['cursor']),
                                    'cursor_final' => $reply['cursor'],
                                    'fields' => $collected,
                                ]);
                                return;
                            }
                            $redis->hScan($key, $reply['cursor'], [], $loop);
                        };
                        $redis->hScan($key, '0', [], $loop);
                    }
                });
            }
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('string', $result['cursor_type']);
        $this->assertSame('0', $result['cursor_final']);
        ksort($result['fields']);
        $this->assertSame([
            'f1' => 'v1',
            'f2' => 'v2',
            'f3' => 'v3',
            'f4' => 'v4',
            'f5' => 'v5',
        ], $result['fields']);
    }

    public function test_hscan_with_match_filters_fields_by_pattern(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hscan:t2:hash';
            $redis->del($key);
            $remaining = 3;
            foreach (['a1' => '1', 'a2' => '2', 'b1' => '3'] as $f => $v) {
                $redis->hSet($key, $f, $v, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $collected = [];
                        $loop = null;
                        $loop = function ($reply) use (&$loop, &$collected, $redis, $emit, $key) {
                            if (!is_array($reply) || !isset($reply['cursor'])) {
                                $emit(['error' => 'bad reply', 'reply' => $reply]);
                                return;
                            }
                            foreach ($reply['fields'] as $f => $v) {
                                $collected[$f] = $v;
                            }
                            if ($reply['cursor'] === '0') {
                                $emit($collected);
                                return;
                            }
                            $redis->hScan($key, $reply['cursor'], ['MATCH' => 'a*'], $loop);
                        };
                        $redis->hScan($key, '0', ['MATCH' => 'a*'], $loop);
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

    public function test_hscan_with_count_respects_the_hint(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hscan:t3:hash';
            $redis->del($key);
            $remaining = 30;
            for ($i = 1; $i <= 30; $i++) {
                $redis->hSet($key, 'f'.$i, (string)$i, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $redis->hScan($key, '0', ['COUNT' => 10], function ($reply) use ($emit) {
                            $emit([
                                'cursor' => $reply['cursor'] ?? null,
                                'count'  => is_array($reply['fields'] ?? null) ? count($reply['fields']) : -1,
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

    public function test_hscanall_iterates_the_full_hash(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hscanall:t4:hash';
            $redis->del($key);
            $total = 150;
            $remaining = $total;
            for ($i = 1; $i <= $total; $i++) {
                $redis->hSet($key, 'field'.$i, 'value'.$i, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $redis->hScanAll($key, ['COUNT' => 25], function ($all) use ($emit) {
                            $emit([
                                'count'  => is_array($all) ? count($all) : -1,
                                'unique' => is_array($all) ? count(array_unique(array_keys($all))) : -1,
                                'sample' => is_array($all) ? ($all['field42'] ?? null) : null,
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
        $this->assertSame('value42', $result['sample']);
    }

    public function test_hscanall_honors_the_limit_option(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hscanall:t5:hash';
            $redis->del($key);
            $total = 100;
            $remaining = $total;
            for ($i = 1; $i <= $total; $i++) {
                $redis->hSet($key, 'field'.$i, (string)$i, function () use (&$remaining, $redis, $emit, $key) {
                    $remaining--;
                    if ($remaining === 0) {
                        $redis->hScanAll($key, ['COUNT' => 25, 'limit' => 30], function ($all) use ($emit) {
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
        $this->assertLessThanOrEqual(55, $result['count']);
        $this->assertGreaterThan(0, $result['count']);
    }

    public function test_hscan_on_a_missing_key_returns_empty_fields(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hscan:missing:xxxxxxxx:hash';
            $redis->del($key);
            $redis->hScan($key, '0', [], function ($reply) use ($emit) {
                $emit([
                    'cursor'      => $reply['cursor'] ?? null,
                    'cursor_type' => isset($reply['cursor']) ? gettype($reply['cursor']) : null,
                    'fields'      => $reply['fields'] ?? null,
                ]);
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('0', $result['cursor']);
        $this->assertSame('string', $result['cursor_type']);
        $this->assertSame([], $result['fields']);
    }

    public function test_hscan_with_malformed_cursor_passes_through_the_error(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:hscan:malformed:hash';
            $redis->del($key);
            $redis->hSet($key, 'f', 'v', function () use ($redis, $emit, $key) {
                $redis->hScan($key, 'not-a-number', [], function ($reply) use ($emit) {
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
        // The result must NOT be the normal ['cursor' => ..., 'fields' => ...] shape.
        $this->assertSame(false, $result['is_array']);
    }
}
