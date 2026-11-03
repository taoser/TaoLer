<?php

/*
|--------------------------------------------------------------------------
| String / counter commands (Group 4 §4.1)
|--------------------------------------------------------------------------
|
| Core string and counter verbs with no prior dedicated Feature assertion:
|
|   APPEND, STRLEN, SETRANGE, GETRANGE, GETSET, INCRBY, DECRBY,
|   INCRBYFLOAT, SETEX, PSETEX, SETNX, GETBIT/SETBIT,
|   MSET/MGET (explicit mapCb path), MSETNX.
|
| NOTE: getMultiple() (the phpredis MGET alias) is now a real method and has
| its own dedicated coverage in tests/Feature/GetMultipleTest.php.
|
| All keys use a pest:g4:str:<n>: prefix.
|
| No engine divergences observed for this group — replies are byte-for-byte
| identical across Redis and Dragonfly (floats arrive as bulk strings).
*/

final class StringsCountersTest extends \Tests\RedisTestCase
{
    public function test_append_extends_a_string_and_returns_the_new_length(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:str:1:k', function () use ($redis, $emit) {
                $redis->append('pest:g4:str:1:k', 'foo', function ($len1) use ($redis, $emit) {
                    $redis->append('pest:g4:str:1:k', 'bar', function ($len2) use ($redis, $emit, $len1) {
                        $redis->get('pest:g4:str:1:k', function ($value) use ($emit, $len1, $len2) {
                            $emit(['len1' => $len1, 'len2' => $len2, 'value' => $value]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(3, $result['len1']);
        $this->assertSame(6, $result['len2']);
        $this->assertSame('foobar', $result['value']);
    }

    public function test_strlen_returns_the_byte_length_of_the_stored_value(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:g4:str:2:k', 'hello', function () use ($redis, $emit) {
                $redis->strLen('pest:g4:str:2:k', function ($len) use ($emit) {
                    $emit($len);
                });
            });
PHP
        );

        $this->assertSame(5, $result);
    }

    public function test_setrange_overwrites_part_of_a_string_at_an_offset(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:g4:str:3:k', 'Hello World', function () use ($redis, $emit) {
                // Overwrite "World" starting at offset 6.
                $redis->setRange('pest:g4:str:3:k', 6, 'Redis', function ($len) use ($redis, $emit) {
                    $redis->get('pest:g4:str:3:k', function ($value) use ($emit, $len) {
                        $emit(['len' => $len, 'value' => $value]);
                    });
                });
            });
PHP
        );

        $this->assertSame(11, $result['len']);
        $this->assertSame('Hello Redis', $result['value']);
    }

    public function test_getrange_returns_a_substring_by_inclusive_byte_offsets(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:g4:str:4:k', 'Hello World', function () use ($redis, $emit) {
                $redis->getRange('pest:g4:str:4:k', 0, 4, function ($slice) use ($emit) {
                    $emit($slice);
                });
            });
PHP
        );

        $this->assertSame('Hello', $result);
    }

    public function test_getset_swaps_the_value_and_returns_the_previous_one(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:g4:str:5:k', 'old', function () use ($redis, $emit) {
                $redis->getSet('pest:g4:str:5:k', 'new', function ($prev) use ($redis, $emit) {
                    $redis->get('pest:g4:str:5:k', function ($now) use ($emit, $prev) {
                        $emit(['prev' => $prev, 'now' => $now]);
                    });
                });
            });
PHP
        );

        $this->assertSame('old', $result['prev']);
        $this->assertSame('new', $result['now']);
    }

    public function test_incrby_and_decrby_adjust_an_integer_counter(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:str:6:k', function () use ($redis, $emit) {
                $redis->incrBy('pest:g4:str:6:k', 10, function ($afterIncr) use ($redis, $emit) {
                    $redis->decrBy('pest:g4:str:6:k', 3, function ($afterDecr) use ($emit, $afterIncr) {
                        $emit(['incr' => $afterIncr, 'decr' => $afterDecr]);
                    });
                });
            });
PHP
        );

        $this->assertSame(10, $result['incr']);
        $this->assertSame(7, $result['decr']);
    }

    public function test_incrbyfloat_adds_a_fractional_amount_and_returns_a_string(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:g4:str:7:k', '10', function () use ($redis, $emit) {
                $redis->incrByFloat('pest:g4:str:7:k', 2.5, function ($value) use ($emit) {
                    $emit($value);
                });
            });
PHP
        );

        // INCRBYFLOAT replies with a bulk string, not a RESP integer.
        $this->assertSame('12.5', $result);
    }

    public function test_setex_stores_a_value_with_a_second_ttl(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:str:8:k', function () use ($redis, $emit) {
                $redis->setEx('pest:g4:str:8:k', 100, 'v', function ($ok) use ($redis, $emit) {
                    $redis->get('pest:g4:str:8:k', function ($value) use ($redis, $emit, $ok) {
                        $redis->ttl('pest:g4:str:8:k', function ($ttl) use ($emit, $ok, $value) {
                            $emit(['ok' => $ok, 'value' => $value, 'ttl' => $ttl]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertTrue($result['ok']);
        $this->assertSame('v', $result['value']);
        $this->assertGreaterThan(0, $result['ttl']);
        $this->assertLessThanOrEqual(100, $result['ttl']);
    }

    public function test_psetex_stores_a_value_with_a_millisecond_ttl(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:str:9:k', function () use ($redis, $emit) {
                $redis->pSetEx('pest:g4:str:9:k', 100000, 'v', function ($ok) use ($redis, $emit) {
                    $redis->pttl('pest:g4:str:9:k', function ($pttl) use ($emit, $ok) {
                        $emit(['ok' => $ok, 'pttl' => $pttl]);
                    });
                });
            });
PHP
        );

        $this->assertTrue($result['ok']);
        $this->assertGreaterThan(0, $result['pttl']);
        $this->assertLessThanOrEqual(100000, $result['pttl']);
    }

    public function test_setnx_only_sets_when_the_key_is_absent(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:str:10:k', function () use ($redis, $emit) {
                $redis->setNx('pest:g4:str:10:k', 'first', function ($firstSet) use ($redis, $emit) {
                    // Second attempt must be refused.
                    $redis->setNx('pest:g4:str:10:k', 'second', function ($secondSet) use ($redis, $emit, $firstSet) {
                        $redis->get('pest:g4:str:10:k', function ($value) use ($emit, $firstSet, $secondSet) {
                            $emit(['first' => $firstSet, 'second' => $secondSet, 'value' => $value]);
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

    public function test_setbit_and_getbit_flip_and_read_individual_bits(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:str:11:k', function () use ($redis, $emit) {
                $redis->setBit('pest:g4:str:11:k', 7, 1, function ($prevBit) use ($redis, $emit) {
                    $redis->getBit('pest:g4:str:11:k', 7, function ($bit7) use ($redis, $emit, $prevBit) {
                        $redis->getBit('pest:g4:str:11:k', 6, function ($bit6) use ($emit, $prevBit, $bit7) {
                            $emit(['prev' => $prevBit, 'bit7' => $bit7, 'bit6' => $bit6]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(0, $result['prev']);
        $this->assertSame(1, $result['bit7']);
        $this->assertSame(0, $result['bit6']);
    }

    public function test_mset_then_mget_round_trips_multiple_keys_via_the_mapcb_path(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:str:12:a', 'pest:g4:str:12:b', function () use ($redis, $emit) {
                $redis->mSet(['pest:g4:str:12:a' => '1', 'pest:g4:str:12:b' => '2'], function ($ok) use ($redis, $emit) {
                    $redis->mGet(['pest:g4:str:12:a', 'pest:g4:str:12:b', 'pest:g4:str:12:missing'], function ($values) use ($emit, $ok) {
                        $emit(['ok' => $ok, 'values' => $values]);
                    });
                });
            });
PHP
        );

        // MSET returns +OK -> true.
        $this->assertTrue($result['ok']);
        $this->assertSame(['1', '2', null], $result['values']);
    }

    public function test_msetnx_sets_all_keys_only_when_none_exist(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:str:13:a', 'pest:g4:str:13:b', function () use ($redis, $emit) {
                // First call: neither key exists -> sets both, returns 1.
                $redis->mSetNx(['pest:g4:str:13:a' => '1', 'pest:g4:str:13:b' => '2'], function ($firstOk) use ($redis, $emit) {
                    // Second call: 'a' now exists -> sets nothing, returns 0.
                    $redis->mSetNx(['pest:g4:str:13:a' => 'x', 'pest:g4:str:13:c' => 'y'], function ($secondOk) use ($redis, $emit, $firstOk) {
                        $redis->exists('pest:g4:str:13:c', function ($cExists) use ($emit, $firstOk, $secondOk) {
                            $emit(['first' => $firstOk, 'second' => $secondOk, 'cExists' => $cExists]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['first']);
        $this->assertSame(0, $result['second']);
        // 'c' must not have been created because the whole MSETNX was refused.
        $this->assertSame(0, $result['cExists']);
    }
}
