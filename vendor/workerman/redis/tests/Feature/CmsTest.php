<?php

/*
|--------------------------------------------------------------------------
| Count-Min Sketch / CMS.* module
|--------------------------------------------------------------------------
|
| Dragonfly natively implements the RedisBloom Count-Min-Sketch command
| set. These tests exercise the cms() dispatcher and the typed shortcuts
| (cmsInitByDim, cmsInitByProb, cmsIncrBy, cmsQuery, cmsMerge, cmsInfo)
| added to Client.php.
|
| Each test uses a unique pest:cms:tN: prefix so concurrent runs don't
| collide.
*/

final class CmsTest extends \Tests\RedisTestCase
{
    public function test_cmsinitbydim_creates_a_sketch_that_cmsquery_can_read_from(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:cms:t1:sketch', function () use ($redis, $emit) {
                $redis->cmsInitByDim('pest:cms:t1:sketch', 1000, 5, function ($ok) use ($redis, $emit) {
                    $redis->cmsIncrBy('pest:cms:t1:sketch', 'apple', 4, function () use ($ok, $redis, $emit) {
                        $redis->cmsQuery('pest:cms:t1:sketch', 'apple', 'pear', function ($q) use ($ok, $emit) {
                            $emit(['init' => $ok, 'query' => $q]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertTrue($result['init']);
        // pear was never added — exact zero (Count-Min never under-counts).
        $this->assertSame([4, 0], $result['query']);
    }

    public function test_cmsinitbyprob_creates_a_sketch_sized_for_a_target_error_rate(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:cms:t2:sketch', function () use ($redis, $emit) {
                $redis->cmsInitByProb('pest:cms:t2:sketch', 0.001, 0.01, function ($ok) use ($redis, $emit) {
                    $redis->cmsIncrBy('pest:cms:t2:sketch', 'banana', 7, function () use ($ok, $redis, $emit) {
                        $redis->cmsQuery('pest:cms:t2:sketch', 'banana', function ($q) use ($ok, $emit) {
                            $emit(['init' => $ok, 'query' => $q]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertTrue($result['init']);
        $this->assertSame([7], $result['query']);
    }

    public function test_cmsincrby_returns_the_new_counts_for_each_item(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:cms:t3:sketch', function () use ($redis, $emit) {
                $redis->cmsInitByDim('pest:cms:t3:sketch', 1000, 5, function () use ($redis, $emit) {
                    $redis->cmsIncrBy('pest:cms:t3:sketch', 'a', 3, 'b', 5, function ($reply) use ($emit) {
                        $emit(['incr' => $reply]);
                    });
                });
            });
PHP
        );

        // CMS.INCRBY returns the post-increment estimate per item, ordered.
        $this->assertSame([3, 5], $result['incr']);
    }

    public function test_cmsquery_returns_0_for_never_incremented_items(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:cms:t4:sketch', function () use ($redis, $emit) {
                $redis->cmsInitByDim('pest:cms:t4:sketch', 1000, 5, function () use ($redis, $emit) {
                    $redis->cmsIncrBy('pest:cms:t4:sketch', 'seen', 2, function () use ($redis, $emit) {
                        $redis->cmsQuery('pest:cms:t4:sketch', 'seen', 'unseen', function ($reply) use ($emit) {
                            $emit(['query' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame([2, 0], $result['query']);
    }

    public function test_cmsmerge_sums_counts_from_multiple_source_sketches_into_a_destination(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del(
                'pest:cms:t5:s1', 'pest:cms:t5:s2', 'pest:cms:t5:dst',
                function () use ($redis, $emit) {
                    $redis->cmsInitByDim('pest:cms:t5:s1', 1000, 5, function () use ($redis, $emit) {
                        $redis->cmsInitByDim('pest:cms:t5:s2', 1000, 5, function () use ($redis, $emit) {
                            $redis->cmsInitByDim('pest:cms:t5:dst', 1000, 5, function () use ($redis, $emit) {
                                $redis->cmsIncrBy('pest:cms:t5:s1', 'x', 4, function () use ($redis, $emit) {
                                    $redis->cmsIncrBy('pest:cms:t5:s2', 'x', 6, function () use ($redis, $emit) {
                                        $redis->cmsMerge(
                                            'pest:cms:t5:dst',
                                            2,
                                            ['pest:cms:t5:s1', 'pest:cms:t5:s2'],
                                            null,
                                            function ($ok) use ($redis, $emit) {
                                                $redis->cmsQuery('pest:cms:t5:dst', 'x', function ($q) use ($ok, $emit) {
                                                    $emit(['merge' => $ok, 'query' => $q]);
                                                });
                                            }
                                        );
                                    });
                                });
                            });
                        });
                    });
                }
            );
PHP
        );

        $this->assertTrue($result['merge']);
        $this->assertSame([10], $result['query']);
    }

    public function test_cmsinfo_returns_sketch_dimensions_and_a_running_count(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:cms:t6:sketch', function () use ($redis, $emit) {
                $redis->cmsInitByDim('pest:cms:t6:sketch', 1000, 5, function () use ($redis, $emit) {
                    $redis->cmsIncrBy('pest:cms:t6:sketch', 'item', 3, function () use ($redis, $emit) {
                        $redis->cmsInfo('pest:cms:t6:sketch', function ($info) use ($emit) {
                            $emit(['info' => $info]);
                        });
                    });
                });
            });
PHP
        );

        // Dragonfly returns CMS.INFO as a flat [name, value, ...] array.
        $this->assertIsArray($result['info']);
        $info = [];
        for ($i = 0, $n = count($result['info']); $i + 1 < $n; $i += 2) {
            $info[(string) $result['info'][$i]] = $result['info'][$i + 1];
        }
        $this->assertArrayHasKey('width', $info);
        $this->assertArrayHasKey('depth', $info);
        $this->assertSame(1000, (int) $info['width']);
        $this->assertSame(5, (int) $info['depth']);
    }
}
