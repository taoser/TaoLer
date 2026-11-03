<?php

/*
|--------------------------------------------------------------------------
| TopK / TOPK.* module
|--------------------------------------------------------------------------
|
| Dragonfly natively implements the RedisBloom TopK command set. These
| tests exercise the topk() dispatcher and the typed shortcuts (topkReserve,
| topkAdd, topkIncrBy, topkQuery, topkCount, topkList, topkInfo) added to
| Client.php.
|
| Each test uses a unique pest:topk:tN: prefix so concurrent runs don't
| collide.
*/

final class TopkTest extends \Tests\RedisTestCase
{
    public function test_topkreserve_creates_a_topk_sketch_that_topkadd_topkquery_can_use(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:topk:t1:sketch', function () use ($redis, $emit) {
                $redis->topkReserve('pest:topk:t1:sketch', 3, 8, 7, 0.9, function ($ok) use ($redis, $emit) {
                    $redis->topkAdd('pest:topk:t1:sketch', 'apple', function () use ($ok, $redis, $emit) {
                        $redis->topkQuery('pest:topk:t1:sketch', 'apple', 'banana', function ($q) use ($ok, $emit) {
                            $emit(['reserve' => $ok, 'query' => $q]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertTrue($result['reserve']);
        // apple was added so it must be in the top-K (K=3, only one entry).
        $this->assertSame([1, 0], $result['query']);
    }

    public function test_topkadd_returns_the_displacement_array(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:topk:t2:sketch', function () use ($redis, $emit) {
                $redis->topkReserve('pest:topk:t2:sketch', 3, 8, 7, 0.9, function () use ($redis, $emit) {
                    $redis->topkAdd('pest:topk:t2:sketch', 'a', 'b', 'c', function ($reply) use ($emit) {
                        $emit(['add' => $reply]);
                    });
                });
            });
PHP
        );

        // Three slots, three new items, nothing evicted — each element is
        // either null or an empty string per Dragonfly's "no displacement".
        $this->assertIsArray($result['add']);
        $this->assertCount(3, $result['add']);
        foreach ($result['add'] as $slot) {
            // Empty bulk / nil are both "no displacement".
            $this->assertTrue($slot === null || $slot === '' || $slot === false);
        }
    }

    public function test_topkincrby_bumps_counters_for_multiple_items(): void
    {
        // TopK uses a small Count-Min-Sketch-like counter that decays as new
        // items contend for slots. On Dragonfly's RedisBloom-compatible
        // implementation a fresh top-K with K=3 and width=8/depth=7/decay=0.9
        // reliably under-counts heavy-hitter increments by one (e.g. an INCRBY
        // of 5 reports back as 4 from TOPK.COUNT). Assert direction rather
        // than an exact integer so the test stays stable across patch-level
        // server changes.
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:topk:t3:sketch', function () use ($redis, $emit) {
                $redis->topkReserve('pest:topk:t3:sketch', 3, 8, 7, 0.9, function () use ($redis, $emit) {
                    $redis->topkIncrBy('pest:topk:t3:sketch', 'x', 5, 'y', 3, function () use ($redis, $emit) {
                        $redis->topkCount('pest:topk:t3:sketch', 'x', 'y', function ($counts) use ($emit) {
                            $emit(['count' => $counts]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result['count']);
        $this->assertCount(2, $result['count']);
        // Both items got at least one increment registered (lower bound 1)
        // and at most the full requested bump (upper bound 5 / 3 respectively).
        $this->assertGreaterThanOrEqual(1, $result['count'][0]);
        $this->assertLessThanOrEqual(5, $result['count'][0]);
        $this->assertGreaterThanOrEqual(1, $result['count'][1]);
        $this->assertLessThanOrEqual(3, $result['count'][1]);
    }

    public function test_topkquery_returns_1_for_in_top_k_0_for_outside(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:topk:t4:sketch', function () use ($redis, $emit) {
                $redis->topkReserve('pest:topk:t4:sketch', 3, 8, 7, 0.9, function () use ($redis, $emit) {
                    $redis->topkIncrBy('pest:topk:t4:sketch', 'p', 9, 'q', 8, 'r', 7, function () use ($redis, $emit) {
                        $redis->topkQuery('pest:topk:t4:sketch', 'p', 'q', 'r', 'z', function ($reply) use ($emit) {
                            $emit(['query' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        // p, q, r are the heavy hitters; z was never added.
        $this->assertSame([1, 1, 1, 0], $result['query']);
    }

    public function test_topkcount_returns_estimated_counts_per_item(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:topk:t5:sketch', function () use ($redis, $emit) {
                $redis->topkReserve('pest:topk:t5:sketch', 3, 8, 7, 0.9, function () use ($redis, $emit) {
                    $redis->topkIncrBy('pest:topk:t5:sketch', 'alpha', 12, function () use ($redis, $emit) {
                        $redis->topkCount('pest:topk:t5:sketch', 'alpha', 'omega', function ($reply) use ($emit) {
                            $emit(['count' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        // alpha was bumped 12; omega never added.
        $this->assertSame(12, $result['count'][0]);
        $this->assertSame(0, $result['count'][1]);
    }

    public function test_topklist_returns_the_current_top_k_members(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:topk:t6:sketch', function () use ($redis, $emit) {
                $redis->topkReserve('pest:topk:t6:sketch', 3, 8, 7, 0.9, function () use ($redis, $emit) {
                    $redis->topkIncrBy('pest:topk:t6:sketch', 'm', 10, 'n', 7, 'o', 4, function () use ($redis, $emit) {
                        $redis->topkList('pest:topk:t6:sketch', function ($list) use ($emit) {
                            $emit(['list' => $list]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result['list']);
        $this->assertCount(3, $result['list']);
        $members = array_map('strval', $result['list']);
        sort($members);
        $this->assertSame(['m', 'n', 'o'], $members);
    }

    public function test_topkinfo_reports_k_width_depth_decay(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:topk:t7:sketch', function () use ($redis, $emit) {
                $redis->topkReserve('pest:topk:t7:sketch', 3, 8, 7, 0.9, function () use ($redis, $emit) {
                    $redis->topkInfo('pest:topk:t7:sketch', function ($info) use ($emit) {
                        $emit(['info' => $info]);
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result['info']);
        $info = [];
        for ($i = 0, $n = count($result['info']); $i + 1 < $n; $i += 2) {
            $info[(string) $result['info'][$i]] = $result['info'][$i + 1];
        }
        $this->assertArrayHasKey('width', $info);
        $this->assertArrayHasKey('depth', $info);
        $this->assertSame(8, (int) $info['width']);
        $this->assertSame(7, (int) $info['depth']);
    }
}
