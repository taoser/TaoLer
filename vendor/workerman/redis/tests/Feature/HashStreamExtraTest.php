<?php

/*
|--------------------------------------------------------------------------
| Hash & Stream core commands (Group 4 §4.2)
|--------------------------------------------------------------------------
|
| Hash and stream verbs lacking a dedicated Feature assertion:
|
|   Hashes:  HSET/HGET, HGETALL, HDEL, HEXISTS, HKEYS, HVALS, HLEN,
|            HINCRBY, HINCRBYFLOAT, HMSET (explicit), HMGET (explicit),
|            HSETNX, HSTRLEN
|   Streams: XADD, XLEN, XRANGE, XREVRANGE, XREAD, XDEL, XTRIM
|
| Keys use a pest:g4:hs:<n>: prefix. No engine divergences observed.
*/

final class HashStreamExtraTest extends \Tests\RedisTestCase
{
    /* --------------------------------------------------------------- Hashes */

    public function test_hset_hget_hexists_hlen_handle_individual_fields(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:1:h', function () use ($redis, $emit) {
                $redis->hSet('pest:g4:hs:1:h', 'f1', 'v1', function ($added) use ($redis, $emit) {
                    $redis->hGet('pest:g4:hs:1:h', 'f1', function ($value) use ($redis, $emit, $added) {
                        $redis->hExists('pest:g4:hs:1:h', 'f1', function ($exists) use ($redis, $emit, $added, $value) {
                            $redis->hLen('pest:g4:hs:1:h', function ($len) use ($emit, $added, $value, $exists) {
                                $emit(['added' => $added, 'value' => $value, 'exists' => $exists, 'len' => $len]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['added']);
        $this->assertSame('v1', $result['value']);
        $this->assertSame(1, $result['exists']);
        $this->assertSame(1, $result['len']);
    }

    public function test_hmset_hgetall_round_trip_an_associative_map(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:2:h', function () use ($redis, $emit) {
                $redis->hMSet('pest:g4:hs:2:h', ['a' => '1', 'b' => '2', 'c' => '3'], function ($ok) use ($redis, $emit) {
                    $redis->hGetAll('pest:g4:hs:2:h', function ($all) use ($emit, $ok) {
                        $emit(['ok' => $ok, 'all' => $all]);
                    });
                });
            });
PHP
        );

        $this->assertTrue($result['ok']);
        // hGetAll() formats the flat reply into an associative array.
        $this->assertSame(['a' => '1', 'b' => '2', 'c' => '3'], $result['all']);
    }

    public function test_hmget_returns_the_requested_fields_keyed_by_field_name(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:3:h', function () use ($redis, $emit) {
                $redis->hMSet('pest:g4:hs:3:h', ['a' => '1', 'b' => '2'], function () use ($redis, $emit) {
                    $redis->hMGet('pest:g4:hs:3:h', ['a', 'b', 'missing'], function ($values) use ($emit) {
                        $emit($values);
                    });
                });
            });
PHP
        );

        // hMGet() combines the field names with the reply values.
        $this->assertSame(['a' => '1', 'b' => '2', 'missing' => null], $result);
    }

    public function test_hkeys_hvals_list_field_names_and_values(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:4:h', function () use ($redis, $emit) {
                $redis->hMSet('pest:g4:hs:4:h', ['a' => '1', 'b' => '2'], function () use ($redis, $emit) {
                    $redis->hKeys('pest:g4:hs:4:h', function ($keys) use ($redis, $emit) {
                        $redis->hVals('pest:g4:hs:4:h', function ($vals) use ($emit, $keys) {
                            sort($keys); sort($vals);
                            $emit(['keys' => $keys, 'vals' => $vals]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(['a', 'b'], $result['keys']);
        $this->assertSame(['1', '2'], $result['vals']);
    }

    public function test_hdel_removes_fields_and_returns_the_count_deleted(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:5:h', function () use ($redis, $emit) {
                $redis->hMSet('pest:g4:hs:5:h', ['a' => '1', 'b' => '2', 'c' => '3'], function () use ($redis, $emit) {
                    $redis->hDel('pest:g4:hs:5:h', 'a', 'b', 'missing', function ($deleted) use ($redis, $emit) {
                        $redis->hLen('pest:g4:hs:5:h', function ($len) use ($emit, $deleted) {
                            $emit(['deleted' => $deleted, 'len' => $len]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result['deleted']);
        $this->assertSame(1, $result['len']);
    }

    public function test_hincrby_and_hincrbyfloat_adjust_numeric_fields(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:6:h', function () use ($redis, $emit) {
                $redis->hSet('pest:g4:hs:6:h', 'n', '10', function () use ($redis, $emit) {
                    $redis->hIncrBy('pest:g4:hs:6:h', 'n', 5, function ($afterInt) use ($redis, $emit) {
                        $redis->hIncrByFloat('pest:g4:hs:6:h', 'n', 0.5, function ($afterFloat) use ($emit, $afterInt) {
                            $emit(['int' => $afterInt, 'float' => $afterFloat]);
                        });
                    });
                });
            });
PHP
        );

        // HINCRBY returns an integer; HINCRBYFLOAT a bulk string.
        $this->assertSame(15, $result['int']);
        $this->assertSame('15.5', $result['float']);
    }

    public function test_hsetnx_only_writes_a_field_when_it_is_absent(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:7:h', function () use ($redis, $emit) {
                $redis->hSetNx('pest:g4:hs:7:h', 'f', 'first', function ($firstOk) use ($redis, $emit) {
                    $redis->hSetNx('pest:g4:hs:7:h', 'f', 'second', function ($secondOk) use ($redis, $emit, $firstOk) {
                        $redis->hGet('pest:g4:hs:7:h', 'f', function ($value) use ($emit, $firstOk, $secondOk) {
                            $emit(['first' => $firstOk, 'second' => $secondOk, 'value' => $value]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['first']);
        $this->assertSame(0, $result['second']);
        $this->assertSame('first', $result['value']);
    }

    public function test_hstrlen_returns_the_byte_length_of_a_field_value(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:8:h', function () use ($redis, $emit) {
                $redis->hSet('pest:g4:hs:8:h', 'f', 'hello', function () use ($redis, $emit) {
                    $redis->hStrLen('pest:g4:hs:8:h', 'f', function ($len) use ($emit) {
                        $emit($len);
                    });
                });
            });
PHP
        );

        $this->assertSame(5, $result);
    }

    /* -------------------------------------------------------------- Streams */

    public function test_xadd_xlen_xrange_add_and_read_stream_entries_in_order(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:9:stream', function () use ($redis, $emit) {
                $redis->xAdd('pest:g4:hs:9:stream', '1-1', ['k' => 'v1'], function () use ($redis, $emit) {
                    $redis->xAdd('pest:g4:hs:9:stream', '2-1', ['k' => 'v2'], function () use ($redis, $emit) {
                        $redis->xLen('pest:g4:hs:9:stream', function ($len) use ($redis, $emit) {
                            $redis->xRange('pest:g4:hs:9:stream', '-', '+', function ($entries) use ($emit, $len) {
                                $emit([
                                    'len'      => $len,
                                    'firstId'  => $entries[0][0],
                                    'firstVal' => $entries[0][1],
                                    'lastId'   => $entries[1][0],
                                ]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result['len']);
        $this->assertSame('1-1', $result['firstId']);
        $this->assertSame(['k', 'v1'], $result['firstVal']);
        $this->assertSame('2-1', $result['lastId']);
    }

    public function test_xrevrange_reads_stream_entries_newest_first(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:10:stream', function () use ($redis, $emit) {
                $redis->xAdd('pest:g4:hs:10:stream', '1-1', ['k' => 'v1'], function () use ($redis, $emit) {
                    $redis->xAdd('pest:g4:hs:10:stream', '2-1', ['k' => 'v2'], function () use ($redis, $emit) {
                        $redis->xRevRange('pest:g4:hs:10:stream', '+', '-', function ($entries) use ($emit) {
                            $emit(['firstId' => $entries[0][0], 'secondId' => $entries[1][0]]);
                        });
                    });
                });
            });
PHP
        );

        // Reverse order: newest (2-1) comes first.
        $this->assertSame('2-1', $result['firstId']);
        $this->assertSame('1-1', $result['secondId']);
    }

    public function test_xread_returns_entries_after_a_given_id(): void
    {
        // XREAD COUNT 10 STREAMS key 0  -> all entries with id > 0.
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:11:stream', function () use ($redis, $emit) {
                $redis->xAdd('pest:g4:hs:11:stream', '1-1', ['k' => 'v1'], function () use ($redis, $emit) {
                    $redis->xAdd('pest:g4:hs:11:stream', '2-1', ['k' => 'v2'], function () use ($redis, $emit) {
                        // Wire form: XREAD COUNT n STREAMS key id
                        $redis->xRead(['COUNT', 10, 'STREAMS', 'pest:g4:hs:11:stream', '0'], function ($reply) use ($emit) {
                            $emit($reply);
                        });
                    });
                });
            });
PHP
        );

        // XREAD reply: [[stream-name, [[id, [field, value]], ...]]]
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertSame('pest:g4:hs:11:stream', $result[0][0]);
        // Two entries returned.
        $this->assertCount(2, $result[0][1]);
    }

    public function test_xdel_removes_specific_stream_entries(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:12:stream', function () use ($redis, $emit) {
                $redis->xAdd('pest:g4:hs:12:stream', '1-1', ['k' => 'v1'], function () use ($redis, $emit) {
                    $redis->xAdd('pest:g4:hs:12:stream', '2-1', ['k' => 'v2'], function () use ($redis, $emit) {
                        $redis->xDel('pest:g4:hs:12:stream', '1-1', function ($deleted) use ($redis, $emit) {
                            $redis->xLen('pest:g4:hs:12:stream', function ($len) use ($emit, $deleted) {
                                $emit(['deleted' => $deleted, 'len' => $len]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['deleted']);
        $this->assertSame(1, $result['len']);
    }

    public function test_xtrim_caps_a_stream_to_maxlen(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:hs:13:stream', function () use ($redis, $emit) {
                $redis->xAdd('pest:g4:hs:13:stream', '1-1', ['k' => 'v1'], function () use ($redis, $emit) {
                    $redis->xAdd('pest:g4:hs:13:stream', '2-1', ['k' => 'v2'], function () use ($redis, $emit) {
                        $redis->xAdd('pest:g4:hs:13:stream', '3-1', ['k' => 'v3'], function () use ($redis, $emit) {
                            // Wire form: XTRIM key MAXLEN n
                            $redis->xTrim('pest:g4:hs:13:stream', 'MAXLEN', 1, function ($trimmed) use ($redis, $emit) {
                                $redis->xLen('pest:g4:hs:13:stream', function ($len) use ($emit, $trimmed) {
                                    $emit(['trimmed' => $trimmed, 'len' => $len]);
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        // Three entries trimmed down to one -> two removed.
        $this->assertSame(2, $result['trimmed']);
        $this->assertSame(1, $result['len']);
    }
}
