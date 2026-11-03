<?php

/*
|--------------------------------------------------------------------------
| Tier 2 modern Redis commands (lists/sets/hashes/zsets/streams)
|--------------------------------------------------------------------------
|
| Covers commands that route through Client::__call() because each one has
| two or more wire args (so the count($args) > 1 branch picks up the
| trailing callback). No explicit method — only @method declarations on
| the class docblock and these integration tests confirming each one runs
| end-to-end against a live server.
|
| Lists:        LMOVE, LMPOP, LPOS, BLMOVE, BLMPOP
| Sets:         SMISMEMBER, SINTERCARD
| Hashes:       HRANDFIELD
| Sorted sets:  ZRANDMEMBER, ZMSCORE, ZDIFF, ZDIFFSTORE, ZINTER, ZINTERCARD,
|               ZUNION, ZRANGESTORE, ZMPOP, BZMPOP, ZREVRANGEBYLEX,
|               ZREMRANGEBYLEX, ZLEXCOUNT
| Streams:      XAUTOCLAIM, XSETID
|
| Each test uses a unique pest:modern:tN: prefix to avoid collisions. The
| assertions favour "did the command return a sane shape" over exhaustive
| semantic checks — some replies (LMPOP, ZMPOP, BLMPOP, XAUTOCLAIM) nest
| in implementation-specific ways across Redis/Dragonfly versions.
*/

final class ModernCommandsTest extends \Tests\RedisTestCase
{
    public function test_lmove_moves_an_element_between_two_lists(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t1:src', 'pest:modern:t1:dst', function () use ($redis, $emit) {
                $redis->rPush('pest:modern:t1:src', 'a', 'b', 'c', function () use ($redis, $emit) {
                    $redis->lMove('pest:modern:t1:src', 'pest:modern:t1:dst', 'LEFT', 'RIGHT', function ($moved) use ($emit) {
                        $emit($moved);
                    });
                });
            });
PHP
        );

        $this->assertSame('a', $result);
    }

    public function test_lmpop_pops_elements_from_the_first_non_empty_list(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t2:list', function () use ($redis, $emit) {
                $redis->rPush('pest:modern:t2:list', 'a', 'b', 'c', function () use ($redis, $emit) {
                    // Wire form: LMPOP numkeys key [key ...] LEFT|RIGHT [COUNT n]
                    $redis->lMPop([1, 'pest:modern:t2:list'], 'LEFT', function ($reply) use ($emit) {
                        $emit($reply);
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        // Reply shape: [key, [popped...]]
        $this->assertSame('pest:modern:t2:list', $result[0]);
        $this->assertIsArray($result[1]);
    }

    public function test_lpos_finds_the_index_of_an_element(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t3:list', function () use ($redis, $emit) {
                $redis->rPush('pest:modern:t3:list', 'a', 'b', 'c', 'b', function () use ($redis, $emit) {
                    $redis->lPos('pest:modern:t3:list', 'b', [], function ($index) use ($emit) {
                        $emit($index);
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result);
    }

    public function test_blmove_moves_an_element_with_a_short_timeout_when_data_is_present(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t4:src', 'pest:modern:t4:dst', function () use ($redis, $emit) {
                $redis->rPush('pest:modern:t4:src', 'x', 'y', function () use ($redis, $emit) {
                    // Data already in src — server returns immediately, doesn't block.
                    $redis->blMove('pest:modern:t4:src', 'pest:modern:t4:dst', 'LEFT', 'RIGHT', 0.1, function ($moved) use ($emit) {
                        $emit($moved);
                    });
                });
            });
PHP
        );

        $this->assertSame('x', $result);
    }

    public function test_blmpop_pops_from_a_non_empty_list_within_the_timeout(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t5:list', function () use ($redis, $emit) {
                $redis->rPush('pest:modern:t5:list', 'one', 'two', function () use ($redis, $emit) {
                    // Wire form: BLMPOP timeout numkeys key [key ...] LEFT|RIGHT [COUNT n]
                    $redis->blMPop(0.1, [1, 'pest:modern:t5:list'], 'LEFT', function ($reply) use ($emit) {
                        $emit($reply);
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('pest:modern:t5:list', $result[0]);
    }

    public function test_smismember_returns_one_int_per_member(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t6:set', function () use ($redis, $emit) {
                $redis->sAdd('pest:modern:t6:set', 'a', 'b', function () use ($redis, $emit) {
                    $redis->sMIsMember('pest:modern:t6:set', 'a', 'b', 'c', function ($flags) use ($emit) {
                        $emit($flags);
                    });
                });
            });
PHP
        );

        $this->assertSame([1, 1, 0], $result);
    }

    public function test_sintercard_returns_the_cardinality_of_the_intersection(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t7:a', 'pest:modern:t7:b', function () use ($redis, $emit) {
                $redis->sAdd('pest:modern:t7:a', 'x', 'y', 'z', function () use ($redis, $emit) {
                    $redis->sAdd('pest:modern:t7:b', 'x', 'y', 'q', function () use ($redis, $emit) {
                        // Wire form: SINTERCARD numkeys key [key ...] [LIMIT n]
                        $redis->sInterCard(2, ['pest:modern:t7:a', 'pest:modern:t7:b'], function ($n) use ($emit) {
                            $emit($n);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result);
    }

    public function test_hrandfield_returns_a_field_name_from_the_hash(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t8:hash', function () use ($redis, $emit) {
                $redis->hMSet('pest:modern:t8:hash', ['f1' => 'v1', 'f2' => 'v2', 'f3' => 'v3'], function () use ($redis, $emit) {
                    $redis->hRandField('pest:modern:t8:hash', 1, function ($field) use ($emit) {
                        $emit($field);
                    });
                });
            });
PHP
        );

        // With count=1 the reply is a one-element array containing a field name.
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertContains($result[0], ['f1', 'f2', 'f3']);
    }

    public function test_zrandmember_returns_a_member_of_the_sorted_set(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t9:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t9:z', 1, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t9:z', 2, 'b', function () use ($redis, $emit) {
                        $redis->zRandMember('pest:modern:t9:z', 1, function ($reply) use ($emit) {
                            $emit($reply);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertContains($result[0], ['a', 'b']);
    }

    public function test_zmscore_returns_scores_for_each_requested_member(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t10:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t10:z', 1, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t10:z', 2, 'b', function () use ($redis, $emit) {
                        $redis->zMScore('pest:modern:t10:z', 'a', 'b', 'missing', function ($scores) use ($emit) {
                            $emit($scores);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame('1', $result[0]);
        $this->assertSame('2', $result[1]);
        $this->assertNull($result[2]);
    }

    public function test_zdiff_returns_members_in_the_first_set_but_not_the_others(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t11:a', 'pest:modern:t11:b', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t11:a', 1, 'x', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t11:a', 2, 'y', function () use ($redis, $emit) {
                        $redis->zAdd('pest:modern:t11:b', 1, 'x', function () use ($redis, $emit) {
                            // Wire form: ZDIFF numkeys key [key ...]
                            $redis->zDiff(2, ['pest:modern:t11:a', 'pest:modern:t11:b'], function ($diff) use ($emit) {
                                $emit($diff);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(['y'], $result);
    }

    public function test_zdiffstore_stores_the_diff_and_returns_the_cardinality(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t12:a', 'pest:modern:t12:b', 'pest:modern:t12:dst', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t12:a', 1, 'x', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t12:a', 2, 'y', function () use ($redis, $emit) {
                        $redis->zAdd('pest:modern:t12:b', 1, 'x', function () use ($redis, $emit) {
                            // Wire form: ZDIFFSTORE dst numkeys key [key ...]
                            $redis->zDiffStore('pest:modern:t12:dst', 2, ['pest:modern:t12:a', 'pest:modern:t12:b'], function ($n) use ($emit) {
                                $emit($n);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result);
    }

    public function test_zinter_returns_the_intersection_of_sorted_sets(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t13:a', 'pest:modern:t13:b', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t13:a', 1, 'x', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t13:a', 2, 'y', function () use ($redis, $emit) {
                        $redis->zAdd('pest:modern:t13:b', 1, 'x', function () use ($redis, $emit) {
                            // Wire form: ZINTER numkeys key [key ...]
                            $redis->zInter(2, ['pest:modern:t13:a', 'pest:modern:t13:b'], function ($inter) use ($emit) {
                                $emit($inter);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(['x'], $result);
    }

    public function test_zintercard_returns_the_cardinality_of_the_intersection(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t14:a', 'pest:modern:t14:b', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t14:a', 1, 'x', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t14:a', 2, 'y', function () use ($redis, $emit) {
                        $redis->zAdd('pest:modern:t14:b', 1, 'x', function () use ($redis, $emit) {
                            $redis->zAdd('pest:modern:t14:b', 2, 'y', function () use ($redis, $emit) {
                                $redis->zInterCard(2, ['pest:modern:t14:a', 'pest:modern:t14:b'], function ($n) use ($emit) {
                                    $emit($n);
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result);
    }

    public function test_zunion_returns_the_union_of_sorted_sets(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t15:a', 'pest:modern:t15:b', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t15:a', 1, 'x', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t15:b', 1, 'y', function () use ($redis, $emit) {
                        // Wire form: ZUNION numkeys key [key ...]
                        $redis->zUnion(2, ['pest:modern:t15:a', 'pest:modern:t15:b'], function ($union) use ($emit) {
                            $emit($union);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        sort($result);
        $this->assertSame(['x', 'y'], $result);
    }

    public function test_zrangestore_copies_a_range_from_src_into_dst(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t16:src', 'pest:modern:t16:dst', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t16:src', 1, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t16:src', 2, 'b', function () use ($redis, $emit) {
                        $redis->zAdd('pest:modern:t16:src', 3, 'c', function () use ($redis, $emit) {
                            // Wire form: ZRANGESTORE dst src min max
                            $redis->zRangeStore('pest:modern:t16:dst', 'pest:modern:t16:src', 0, -1, function ($n) use ($emit) {
                                $emit($n);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(3, $result);
    }

    public function test_zmpop_pops_members_from_the_first_non_empty_sorted_set(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t17:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t17:z', 1, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t17:z', 2, 'b', function () use ($redis, $emit) {
                        // Wire form: ZMPOP numkeys key [key ...] MIN|MAX [COUNT n]
                        $redis->zMPop(1, ['pest:modern:t17:z'], 'MIN', function ($reply) use ($emit) {
                            $emit($reply);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('pest:modern:t17:z', $result[0]);
    }

    public function test_bzmpop_pops_members_from_a_non_empty_sorted_set_within_the_timeout(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t18:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t18:z', 1, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t18:z', 2, 'b', function () use ($redis, $emit) {
                        // Wire form: BZMPOP timeout numkeys key [key ...] MIN|MAX [COUNT n]
                        $redis->bzMPop(0.1, 1, ['pest:modern:t18:z'], 'MIN', function ($reply) use ($emit) {
                            $emit($reply);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame('pest:modern:t18:z', $result[0]);
    }

    public function test_zrevrangebylex_returns_members_in_reverse_lexicographic_order(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t19:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t19:z', 0, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t19:z', 0, 'b', function () use ($redis, $emit) {
                        $redis->zAdd('pest:modern:t19:z', 0, 'c', function () use ($redis, $emit) {
                            $redis->zRevRangeByLex('pest:modern:t19:z', '+', '-', function ($members) use ($emit) {
                                $emit($members);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(['c', 'b', 'a'], $result);
    }

    public function test_zremrangebylex_removes_members_in_the_given_lex_range(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t20:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t20:z', 0, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t20:z', 0, 'b', function () use ($redis, $emit) {
                        $redis->zAdd('pest:modern:t20:z', 0, 'c', function () use ($redis, $emit) {
                            // Wire form: ZREMRANGEBYLEX key min max
                            $redis->zRemRangeByLex('pest:modern:t20:z', '[a', '[b', function ($removed) use ($emit) {
                                $emit($removed);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result);
    }

    public function test_zlexcount_counts_members_in_a_lex_range(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t21:z', function () use ($redis, $emit) {
                $redis->zAdd('pest:modern:t21:z', 0, 'a', function () use ($redis, $emit) {
                    $redis->zAdd('pest:modern:t21:z', 0, 'b', function () use ($redis, $emit) {
                        $redis->zAdd('pest:modern:t21:z', 0, 'c', function () use ($redis, $emit) {
                            $redis->zLexCount('pest:modern:t21:z', '-', '+', function ($n) use ($emit) {
                                $emit($n);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(3, $result);
    }

    public function test_xautoclaim_transfers_idle_pel_entries_to_a_new_consumer(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t22:stream', function () use ($redis, $emit) {
                $redis->xAdd('pest:modern:t22:stream', '*', ['k' => 'v'], function () use ($redis, $emit) {
                    $redis->xGroup('CREATE', 'pest:modern:t22:stream', 'g1', '0', function () use ($redis, $emit) {
                        // Consumer 'c1' reads — entry now in PEL.
                        $redis->rawCommand('XREADGROUP', 'GROUP', 'g1', 'c1', 'COUNT', 1, 'STREAMS', 'pest:modern:t22:stream', '>', function () use ($redis, $emit) {
                            // Wire form: XAUTOCLAIM key group consumer min-idle-time start [COUNT n] [JUSTID]
                            $redis->xAutoClaim('pest:modern:t22:stream', 'g1', 'c2', 0, '0', function ($reply) use ($emit) {
                                $emit($reply);
                            });
                        });
                    });
                });
            });
PHP
        );

        // XAUTOCLAIM returns [next-cursor, claimed-entries, deleted-ids?]
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_xsetid_updates_the_last_generated_id_of_a_stream(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t23:stream', function () use ($redis, $emit) {
                $redis->xAdd('pest:modern:t23:stream', '1-1', ['k' => 'v'], function () use ($redis, $emit) {
                    // XSETID requires an id >= current top. Pick one larger than 1-1.
                    $redis->xSetId('pest:modern:t23:stream', '999-0', function ($ok) use ($emit) {
                        $emit($ok);
                    });
                });
            });
PHP
        );

        $this->assertTrue($result);
    }

    public function test_xadd_flattens_an_associative_message_so_field_names_survive_the_wire(): void
    {
        // Regression for the encoder-only-emits-values bug: a ['field' => 'value']
        // message routed through __call() drops the field NAMES (the RESP encoder
        // iterates array args as values only), and the server rejects it. The
        // explicit xAdd() flattens the pairs itself, so XRANGE must read back both
        // the field names and their values in order.
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t24:stream', function () use ($redis, $emit) {
                $redis->xAdd('pest:modern:t24:stream', '*', ['temperature' => '25', 'humidity' => '60'], function ($id) use ($redis, $emit) {
                    $redis->xRange('pest:modern:t24:stream', '-', '+', function ($entries) use ($emit, $id) {
                        // XRANGE reply: [[id, [field, value, field, value, ...]], ...]
                        $emit([
                            'id'     => $id,
                            'fields' => $entries[0][1],
                        ]);
                    });
                });
            });
PHP
        );

        $this->assertIsString($result['id']);
        $this->assertNotSame('', $result['id']);
        $this->assertSame(['temperature', '25', 'humidity', '60'], $result['fields']);
    }

    public function test_xadd_caps_the_stream_with_maxlen_when_a_length_is_given(): void
    {
        // Push four entries with MAXLEN 2 (exact) — only the last two survive,
        // proving the MAXLEN argument lands before the id on the wire.
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:modern:t25:stream', function () use ($redis, $emit) {
                $redis->xAdd('pest:modern:t25:stream', '*', ['n' => '1'], function () use ($redis, $emit) {
                    $redis->xAdd('pest:modern:t25:stream', '*', ['n' => '2'], function () use ($redis, $emit) {
                        $redis->xAdd('pest:modern:t25:stream', '*', ['n' => '3'], 2, function () use ($redis, $emit) {
                            $redis->xAdd('pest:modern:t25:stream', '*', ['n' => '4'], 2, function () use ($redis, $emit) {
                                $redis->xLen('pest:modern:t25:stream', function ($len) use ($emit) {
                                    $emit($len);
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result);
    }
}
