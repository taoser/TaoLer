<?php

/*
|--------------------------------------------------------------------------
| Bloom Filter / BF.* module
|--------------------------------------------------------------------------
|
| Dragonfly natively implements the RedisBloom Bloom-filter command set.
| These tests exercise the bf() dispatcher and the typed shortcuts
| (bfReserve, bfAdd, bfExists, bfMAdd, bfMExists) added to Client.php.
|
| Each test uses a unique pest:bf:tN: prefix so concurrent runs don't
| collide.
*/

final class BloomFilterTest extends \Tests\RedisTestCase
{
    public function test_bfreserve_creates_a_bloom_filter_that_bfadd_bfexists_can_use(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:bf:t1:bloom', function () use ($redis, $emit) {
                $redis->bfReserve('pest:bf:t1:bloom', 0.01, 1000, function ($ok) use ($redis, $emit) {
                    $redis->bfAdd('pest:bf:t1:bloom', 'alice', function ($added) use ($ok, $redis, $emit) {
                        $redis->bfExists('pest:bf:t1:bloom', 'alice', function ($present) use ($ok, $added, $redis, $emit) {
                            $redis->bfExists('pest:bf:t1:bloom', 'bob', function ($missing) use ($ok, $added, $present, $emit) {
                                $emit([
                                    'reserve' => $ok,
                                    'added' => $added,
                                    'alice_in' => $present,
                                    'bob_in' => $missing,
                                ]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertTrue($result['reserve']);
        $this->assertSame(1, $result['added']);
        $this->assertSame(1, $result['alice_in']);
        $this->assertSame(0, $result['bob_in']);
    }

    public function test_bfadd_is_idempotent_second_add_of_the_same_item_returns_0(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:bf:t2:bloom', function () use ($redis, $emit) {
                $redis->bfReserve('pest:bf:t2:bloom', 0.01, 1000, function () use ($redis, $emit) {
                    $redis->bfAdd('pest:bf:t2:bloom', 'carol', function ($first) use ($redis, $emit) {
                        $redis->bfAdd('pest:bf:t2:bloom', 'carol', function ($second) use ($first, $emit) {
                            $emit(['first' => $first, 'second' => $second]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['first']);
        $this->assertSame(0, $result['second']);
    }

    public function test_bfexists_returns_0_for_never_added_members(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:bf:t3:bloom', function () use ($redis, $emit) {
                $redis->bfReserve('pest:bf:t3:bloom', 0.01, 1000, function () use ($redis, $emit) {
                    $redis->bfAdd('pest:bf:t3:bloom', 'dave', function () use ($redis, $emit) {
                        $redis->bfExists('pest:bf:t3:bloom', 'eve', function ($reply) use ($emit) {
                            $emit(['exists' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(0, $result['exists']);
    }

    public function test_bfmadd_inserts_multiple_items_in_one_round_trip(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:bf:t4:bloom', function () use ($redis, $emit) {
                $redis->bfReserve('pest:bf:t4:bloom', 0.01, 1000, function () use ($redis, $emit) {
                    $redis->bfMAdd('pest:bf:t4:bloom', 'a', 'b', 'c', function ($reply) use ($emit) {
                        $emit(['madd' => $reply]);
                    });
                });
            });
PHP
        );

        // All three are new — Bloom filter returns 1 per slot.
        $this->assertSame([1, 1, 1], $result['madd']);
    }

    public function test_bfmexists_tests_multiple_items_in_one_round_trip(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:bf:t5:bloom', function () use ($redis, $emit) {
                $redis->bfReserve('pest:bf:t5:bloom', 0.01, 1000, function () use ($redis, $emit) {
                    $redis->bfMAdd('pest:bf:t5:bloom', 'foo', 'bar', function () use ($redis, $emit) {
                        $redis->bfMExists('pest:bf:t5:bloom', 'foo', 'baz', 'bar', function ($reply) use ($emit) {
                            $emit(['mexists' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        // foo / bar present, baz absent.
        $this->assertSame([1, 0, 1], $result['mexists']);
    }
}
