<?php

/*
|--------------------------------------------------------------------------
| Core list / set / sorted-set commands (Group 4 §4.2)
|--------------------------------------------------------------------------
|
| The "classic" data-type verbs that ModernCommandsTest does not touch and
| that previously appeared only as scaffolding (no dedicated assertion):
|
|   Lists:  LPUSH/RPUSH, LPOP/RPOP, LLEN, LRANGE, LINDEX, LSET,
|           LINSERT, LREM, LTRIM, RPOPLPUSH, LPUSHX/RPUSHX
|   Sets:   SADD/SREM, SMEMBERS, SISMEMBER, SCARD, SPOP, SRANDMEMBER,
|           SMOVE, SINTER, SUNION, SDIFF (+ STORE variants)
|   Zsets:  ZADD, ZRANGE, ZRANGEBYSCORE, ZREM, ZSCORE, ZRANK, ZREVRANK,
|           ZCARD, ZCOUNT, ZINCRBY, ZPOPMIN, ZPOPMAX, ZREVRANGE
|
| Keys use a pest:g4:lsz:<n>: prefix. No engine divergences observed:
| scores and float increments arrive as bulk strings on both engines.
*/

final class ListSetZsetExtraTest extends \Tests\RedisTestCase
{
    /* ---------------------------------------------------------------- Lists */

    public function test_lpush_rpush_build_a_list_read_back_in_order_by_lrange(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:1:l', function () use ($redis, $emit) {
                // rPush appends to the tail; lPush prepends to the head.
                $redis->rPush('pest:g4:lsz:1:l', 'b', 'c', function () use ($redis, $emit) {
                    $redis->lPush('pest:g4:lsz:1:l', 'a', function ($len) use ($redis, $emit) {
                        $redis->lRange('pest:g4:lsz:1:l', 0, -1, function ($items) use ($emit, $len) {
                            $emit(['len' => $len, 'items' => $items]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(3, $result['len']);
        $this->assertSame(['a', 'b', 'c'], $result['items']);
    }

    public function test_lpop_rpop_remove_from_each_end_and_llen_tracks_the_size(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:2:l', function () use ($redis, $emit) {
                $redis->rPush('pest:g4:lsz:2:l', 'a', 'b', 'c', function () use ($redis, $emit) {
                    $redis->lPop('pest:g4:lsz:2:l', function ($head) use ($redis, $emit) {
                        $redis->rPop('pest:g4:lsz:2:l', function ($tail) use ($redis, $emit, $head) {
                            $redis->lLen('pest:g4:lsz:2:l', function ($len) use ($emit, $head, $tail) {
                                $emit(['head' => $head, 'tail' => $tail, 'len' => $len]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame('a', $result['head']);
        $this->assertSame('c', $result['tail']);
        $this->assertSame(1, $result['len']);
    }

    public function test_lindex_and_lset_read_and_rewrite_a_single_position(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:3:l', function () use ($redis, $emit) {
                $redis->rPush('pest:g4:lsz:3:l', 'a', 'b', 'c', function () use ($redis, $emit) {
                    $redis->lIndex('pest:g4:lsz:3:l', 1, function ($before) use ($redis, $emit) {
                        $redis->lSet('pest:g4:lsz:3:l', 1, 'B', function ($ok) use ($redis, $emit, $before) {
                            $redis->lIndex('pest:g4:lsz:3:l', 1, function ($after) use ($emit, $before, $ok) {
                                $emit(['before' => $before, 'ok' => $ok, 'after' => $after]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame('b', $result['before']);
        $this->assertTrue($result['ok']);
        $this->assertSame('B', $result['after']);
    }

    public function test_linsert_places_a_value_before_a_pivot(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:4:l', function () use ($redis, $emit) {
                $redis->rPush('pest:g4:lsz:4:l', 'a', 'c', function () use ($redis, $emit) {
                    $redis->lInsert('pest:g4:lsz:4:l', 'BEFORE', 'c', 'b', function ($len) use ($redis, $emit) {
                        $redis->lRange('pest:g4:lsz:4:l', 0, -1, function ($items) use ($emit, $len) {
                            $emit(['len' => $len, 'items' => $items]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(3, $result['len']);
        $this->assertSame(['a', 'b', 'c'], $result['items']);
    }

    public function test_lrem_removes_matching_elements_and_ltrim_keeps_a_window(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:5:l', function () use ($redis, $emit) {
                $redis->rPush('pest:g4:lsz:5:l', 'x', 'a', 'x', 'b', 'x', function () use ($redis, $emit) {
                    // Remove the first 2 'x' from head.
                    $redis->lRem('pest:g4:lsz:5:l', 2, 'x', function ($removed) use ($redis, $emit) {
                        $redis->lRange('pest:g4:lsz:5:l', 0, -1, function ($afterRem) use ($redis, $emit, $removed) {
                            // Keep only index 0..1 of what remains (a, b, x).
                            $redis->lTrim('pest:g4:lsz:5:l', 0, 1, function ($trimOk) use ($redis, $emit, $removed, $afterRem) {
                                $redis->lRange('pest:g4:lsz:5:l', 0, -1, function ($afterTrim) use ($emit, $removed, $afterRem, $trimOk) {
                                    $emit([
                                        'removed'   => $removed,
                                        'afterRem'  => $afterRem,
                                        'trimOk'    => $trimOk,
                                        'afterTrim' => $afterTrim,
                                    ]);
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result['removed']);
        $this->assertSame(['a', 'b', 'x'], $result['afterRem']);
        $this->assertTrue($result['trimOk']);
        $this->assertSame(['a', 'b'], $result['afterTrim']);
    }

    public function test_rpoplpush_moves_the_tail_of_one_list_to_the_head_of_another(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:6:src', 'pest:g4:lsz:6:dst', function () use ($redis, $emit) {
                $redis->rPush('pest:g4:lsz:6:src', 'a', 'b', 'c', function () use ($redis, $emit) {
                    $redis->rPopLPush('pest:g4:lsz:6:src', 'pest:g4:lsz:6:dst', function ($moved) use ($redis, $emit) {
                        $redis->lRange('pest:g4:lsz:6:dst', 0, -1, function ($dst) use ($emit, $moved) {
                            $emit(['moved' => $moved, 'dst' => $dst]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame('c', $result['moved']);
        $this->assertSame(['c'], $result['dst']);
    }

    public function test_lpushx_rpushx_only_push_when_the_list_already_exists(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:7:missing', 'pest:g4:lsz:7:exists', function () use ($redis, $emit) {
                // No list yet -> rPushX is a no-op returning 0.
                $redis->rPushX('pest:g4:lsz:7:missing', 'x', function ($onMissing) use ($redis, $emit) {
                    $redis->rPush('pest:g4:lsz:7:exists', 'a', function () use ($redis, $emit, $onMissing) {
                        // List exists -> lPushX prepends and returns the new length.
                        $redis->lPushX('pest:g4:lsz:7:exists', 'z', function ($onExisting) use ($redis, $emit, $onMissing) {
                            $redis->lRange('pest:g4:lsz:7:exists', 0, -1, function ($items) use ($emit, $onMissing, $onExisting) {
                                $emit(['onMissing' => $onMissing, 'onExisting' => $onExisting, 'items' => $items]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(0, $result['onMissing']);
        $this->assertSame(2, $result['onExisting']);
        $this->assertSame(['z', 'a'], $result['items']);
    }

    /* ----------------------------------------------------------------- Sets */

    public function test_sadd_smembers_scard_sismember_srem_cover_basic_set_membership(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:8:s', function () use ($redis, $emit) {
                $redis->sAdd('pest:g4:lsz:8:s', 'a', 'b', 'c', function ($added) use ($redis, $emit) {
                    $redis->sCard('pest:g4:lsz:8:s', function ($card) use ($redis, $emit, $added) {
                        $redis->sIsMember('pest:g4:lsz:8:s', 'b', function ($isB) use ($redis, $emit, $added, $card) {
                            $redis->sRem('pest:g4:lsz:8:s', 'b', function ($removed) use ($redis, $emit, $added, $card, $isB) {
                                $redis->sMembers('pest:g4:lsz:8:s', function ($members) use ($emit, $added, $card, $isB, $removed) {
                                    sort($members);
                                    $emit([
                                        'added'   => $added,
                                        'card'    => $card,
                                        'isB'     => $isB,
                                        'removed' => $removed,
                                        'members' => $members,
                                    ]);
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(3, $result['added']);
        $this->assertSame(3, $result['card']);
        $this->assertSame(1, $result['isB']);
        $this->assertSame(1, $result['removed']);
        $this->assertSame(['a', 'c'], $result['members']);
    }

    public function test_spop_and_srandmember_draw_members_from_the_set(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:9:s', function () use ($redis, $emit) {
                $redis->sAdd('pest:g4:lsz:9:s', 'a', 'b', 'c', function () use ($redis, $emit) {
                    $redis->sRandMember('pest:g4:lsz:9:s', function ($rand) use ($redis, $emit) {
                        $redis->sPop('pest:g4:lsz:9:s', function ($popped) use ($redis, $emit, $rand) {
                            $redis->sCard('pest:g4:lsz:9:s', function ($card) use ($emit, $rand, $popped) {
                                $emit(['rand' => $rand, 'popped' => $popped, 'card' => $card]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertContains($result['rand'], ['a', 'b', 'c']);
        $this->assertContains($result['popped'], ['a', 'b', 'c']);
        // One member popped, two remain.
        $this->assertSame(2, $result['card']);
    }

    public function test_smove_relocates_a_member_between_sets(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:10:a', 'pest:g4:lsz:10:b', function () use ($redis, $emit) {
                $redis->sAdd('pest:g4:lsz:10:a', 'x', 'y', function () use ($redis, $emit) {
                    $redis->sMove('pest:g4:lsz:10:a', 'pest:g4:lsz:10:b', 'x', function ($moved) use ($redis, $emit) {
                        $redis->sIsMember('pest:g4:lsz:10:b', 'x', function ($inB) use ($redis, $emit, $moved) {
                            $redis->sIsMember('pest:g4:lsz:10:a', 'x', function ($inA) use ($emit, $moved, $inB) {
                                $emit(['moved' => $moved, 'inB' => $inB, 'inA' => $inA]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['moved']);
        $this->assertSame(1, $result['inB']);
        $this->assertSame(0, $result['inA']);
    }

    public function test_sinter_sunion_sdiff_compute_set_algebra(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:11:a', 'pest:g4:lsz:11:b', function () use ($redis, $emit) {
                $redis->sAdd('pest:g4:lsz:11:a', 'a', 'b', 'c', function () use ($redis, $emit) {
                    $redis->sAdd('pest:g4:lsz:11:b', 'b', 'c', 'd', function () use ($redis, $emit) {
                        $redis->sInter('pest:g4:lsz:11:a', 'pest:g4:lsz:11:b', function ($inter) use ($redis, $emit) {
                            $redis->sUnion('pest:g4:lsz:11:a', 'pest:g4:lsz:11:b', function ($union) use ($redis, $emit, $inter) {
                                $redis->sDiff('pest:g4:lsz:11:a', 'pest:g4:lsz:11:b', function ($diff) use ($emit, $inter, $union) {
                                    sort($inter); sort($union); sort($diff);
                                    $emit(['inter' => $inter, 'union' => $union, 'diff' => $diff]);
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(['b', 'c'], $result['inter']);
        $this->assertSame(['a', 'b', 'c', 'd'], $result['union']);
        $this->assertSame(['a'], $result['diff']);
    }

    public function test_sinterstore_sunionstore_sdiffstore_persist_results_and_return_the_cardinality(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:12:a', 'pest:g4:lsz:12:b', 'pest:g4:lsz:12:i', 'pest:g4:lsz:12:u', 'pest:g4:lsz:12:d', function () use ($redis, $emit) {
                $redis->sAdd('pest:g4:lsz:12:a', 'a', 'b', 'c', function () use ($redis, $emit) {
                    $redis->sAdd('pest:g4:lsz:12:b', 'b', 'c', 'd', function () use ($redis, $emit) {
                        $redis->sInterStore('pest:g4:lsz:12:i', 'pest:g4:lsz:12:a', 'pest:g4:lsz:12:b', function ($iCard) use ($redis, $emit) {
                            $redis->sUnionStore('pest:g4:lsz:12:u', 'pest:g4:lsz:12:a', 'pest:g4:lsz:12:b', function ($uCard) use ($redis, $emit, $iCard) {
                                $redis->sDiffStore('pest:g4:lsz:12:d', 'pest:g4:lsz:12:a', 'pest:g4:lsz:12:b', function ($dCard) use ($emit, $iCard, $uCard) {
                                    $emit(['i' => $iCard, 'u' => $uCard, 'd' => $dCard]);
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result['i']); // {b,c}
        $this->assertSame(4, $result['u']); // {a,b,c,d}
        $this->assertSame(1, $result['d']); // {a}
    }

    /* ---------------------------------------------------------- Sorted sets */

    public function test_zadd_zrange_zcard_zscore_build_and_read_a_sorted_set(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:13:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:g4:lsz:13:z', 1, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:g4:lsz:13:z', 3, 'c', function () use ($redis, $emit) {
                        $redis->zAdd('pest:g4:lsz:13:z', 2, 'b', function () use ($redis, $emit) {
                            $redis->zRange('pest:g4:lsz:13:z', 0, -1, function ($range) use ($redis, $emit) {
                                $redis->zCard('pest:g4:lsz:13:z', function ($card) use ($redis, $emit, $range) {
                                    $redis->zScore('pest:g4:lsz:13:z', 'b', function ($score) use ($emit, $range, $card) {
                                        $emit(['range' => $range, 'card' => $card, 'score' => $score]);
                                    });
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        // Default ZRANGE is ascending by score.
        $this->assertSame(['a', 'b', 'c'], $result['range']);
        $this->assertSame(3, $result['card']);
        // ZSCORE returns a bulk string on both engines.
        $this->assertSame('2', $result['score']);
    }

    public function test_zrangebyscore_filters_by_score_window(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:14:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:g4:lsz:14:z', 1, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:g4:lsz:14:z', 2, 'b', function () use ($redis, $emit) {
                        $redis->zAdd('pest:g4:lsz:14:z', 3, 'c', function () use ($redis, $emit) {
                            // Inclusive 2..3 -> b, c.
                            $redis->zRangeByScore('pest:g4:lsz:14:z', 2, 3, function ($range) use ($emit) {
                                $emit($range);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(['b', 'c'], $result);
    }

    public function test_zrank_zrevrank_zrevrange_report_position_and_reverse_order(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:15:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:g4:lsz:15:z', 1, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:g4:lsz:15:z', 2, 'b', function () use ($redis, $emit) {
                        $redis->zAdd('pest:g4:lsz:15:z', 3, 'c', function () use ($redis, $emit) {
                            $redis->zRank('pest:g4:lsz:15:z', 'a', function ($rankA) use ($redis, $emit) {
                                $redis->zRevRank('pest:g4:lsz:15:z', 'a', function ($revRankA) use ($redis, $emit, $rankA) {
                                    $redis->zRevRange('pest:g4:lsz:15:z', 0, -1, function ($rev) use ($emit, $rankA, $revRankA) {
                                        $emit(['rankA' => $rankA, 'revRankA' => $revRankA, 'rev' => $rev]);
                                    });
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(0, $result['rankA']);    // lowest score -> rank 0
        $this->assertSame(2, $result['revRankA']); // highest reverse index
        $this->assertSame(['c', 'b', 'a'], $result['rev']);
    }

    public function test_zcount_counts_members_within_a_score_range(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:16:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:g4:lsz:16:z', 1, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:g4:lsz:16:z', 2, 'b', function () use ($redis, $emit) {
                        $redis->zAdd('pest:g4:lsz:16:z', 3, 'c', function () use ($redis, $emit) {
                            $redis->zCount('pest:g4:lsz:16:z', 2, 3, function ($n) use ($emit) {
                                $emit($n);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result);
    }

    public function test_zincrby_bumps_a_member_score_and_returns_the_new_score(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:17:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:g4:lsz:17:z', 5, 'm', function () use ($redis, $emit) {
                    $redis->zIncrBy('pest:g4:lsz:17:z', 2.5, 'm', function ($newScore) use ($emit) {
                        $emit($newScore);
                    });
                });
            });
PHP
        );

        // ZINCRBY returns the resulting score as a bulk string.
        $this->assertSame('7.5', $result);
    }

    public function test_zpopmin_zpopmax_pop_the_lowest_and_highest_scored_members(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:18:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:g4:lsz:18:z', 1, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:g4:lsz:18:z', 2, 'b', function () use ($redis, $emit) {
                        $redis->zAdd('pest:g4:lsz:18:z', 3, 'c', function () use ($redis, $emit) {
                            $redis->zPopMin('pest:g4:lsz:18:z', function ($min) use ($redis, $emit) {
                                $redis->zPopMax('pest:g4:lsz:18:z', function ($max) use ($emit, $min) {
                                    $emit(['min' => $min, 'max' => $max]);
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        // ZPOPMIN/MAX reply is a flat [member, score] pair.
        $this->assertSame(['a', '1'], $result['min']);
        $this->assertSame(['c', '3'], $result['max']);
    }

    public function test_zrem_removes_a_member_and_returns_the_count_removed(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:lsz:19:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:g4:lsz:19:z', 1, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:g4:lsz:19:z', 2, 'b', function () use ($redis, $emit) {
                        $redis->zRem('pest:g4:lsz:19:z', 'a', 'missing', function ($removed) use ($redis, $emit) {
                            $redis->zCard('pest:g4:lsz:19:z', function ($card) use ($emit, $removed) {
                                $emit(['removed' => $removed, 'card' => $card]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['removed']);
        $this->assertSame(1, $result['card']);
    }
}
