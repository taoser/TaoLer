<?php

/*
|--------------------------------------------------------------------------
| Tier 4: Bitmap, Geo, and Scripting RO commands
|--------------------------------------------------------------------------
|
| Covers BITOP / BITPOS / BITFIELD plus the read-only Geo and Scripting
| variants. The five _RO verbs (BITFIELD_RO, GEORADIUS_RO,
| GEORADIUSBYMEMBER_RO, EVAL_RO, EVALSHA_RO) have explicit camelCase
| wrappers on Client — __call() would otherwise uppercase them into
| "BITFIELDRO" / "EVALRO" / etc. and the server would reject the verb.
|
| Unique prefix per test: pest:bge:tN: to avoid collisions across runs.
*/

final class BitmapGeoEvalRoTest extends \Tests\RedisTestCase
{
    public function test_bitop_performs_and_across_two_bitmaps(): void
    {
        $result = runInWorker(<<<'PHP'
            // Two bitmaps; expected AND result is the intersection of bits.
            $redis->set('pest:bge:t1:a', "\xff\x0f", function () use ($redis, $emit) {
                $redis->set('pest:bge:t1:b', "\x0f\xff", function () use ($redis, $emit) {
                    $redis->bitOp('AND', 'pest:bge:t1:dest', 'pest:bge:t1:a', 'pest:bge:t1:b', function ($len) use ($redis, $emit) {
                        $redis->get('pest:bge:t1:dest', function ($result) use ($len, $emit) {
                            $emit(['len' => $len, 'bytes' => bin2hex($result)]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result['len']);
        // 0xff AND 0x0f = 0x0f, 0x0f AND 0xff = 0x0f
        $this->assertSame('0f0f', $result['bytes']);
    }

    public function test_bitpos_finds_the_position_of_the_first_set_bit(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:bge:t2:k', "\x00\xff\xf0", function () use ($redis, $emit) {
                $redis->bitPos('pest:bge:t2:k', 1, function ($pos) use ($emit) {
                    $emit($pos);
                });
            });
PHP
        );

        // 0x00 = bits 0-7 clear; 0xff starts at bit 8.
        $this->assertSame(8, $result);
    }

    public function test_bitfield_increments_a_5_bit_signed_counter(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:bge:t3:k', function () use ($redis, $emit) {
                $redis->bitField('pest:bge:t3:k', 'INCRBY', 'i5', 100, 1, function ($values) use ($redis, $emit) {
                    $redis->bitField('pest:bge:t3:k', 'INCRBY', 'i5', 100, 1, function ($values2) use ($values, $emit) {
                        $emit(['first' => $values, 'second' => $values2]);
                    });
                });
            });
PHP
        );

        $this->assertSame([1], $result['first']);
        $this->assertSame([2], $result['second']);
    }

    public function test_bitfieldro_reads_a_5_bit_signed_value(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:bge:t4:k', function () use ($redis, $emit) {
                $redis->bitField('pest:bge:t4:k', 'SET', 'i5', 0, 7, function () use ($redis, $emit) {
                    $redis->bitFieldRo('pest:bge:t4:k', 'GET', 'i5', 0, function ($values) use ($emit) {
                        $emit($values);
                    });
                });
            });
PHP
        );

        $this->assertSame([7], $result);
    }

    public function test_geosearch_finds_members_within_a_radius_from_a_coordinate(): void
    {
        // GEOSEARCH key FROMLONLAT lon lat BYRADIUS r unit [ASC|DESC] — the
        // RESP encoder flattens 1 level of array nesting, so each option
        // group can be passed as a sub-array.
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:bge:t5:geo', function () use ($redis, $emit) {
                $redis->geoAdd('pest:bge:t5:geo', -122.4194, 37.7749, 'sf', -73.9857, 40.7484, 'ny', function () use ($redis, $emit) {
                    $redis->geoSearch('pest:bge:t5:geo', ['FROMLONLAT', -122.0, 37.0], ['BYRADIUS', 500, 'km'], ['ASC'], function ($members) use ($emit) {
                        $emit($members);
                    });
                });
            });
PHP
        );

        // 'sf' is ~110km from (-122, 37); 'ny' is ~4100km — well outside 500km.
        $this->assertContains('sf', $result);
        $this->assertNotContains('ny', $result);
    }

    public function test_georadiusro_lists_members_within_a_radius_read_only(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:bge:t6:geo', function () use ($redis, $emit) {
                $redis->geoAdd('pest:bge:t6:geo', -122.4194, 37.7749, 'sf', function () use ($redis, $emit) {
                    $redis->geoRadiusRo('pest:bge:t6:geo', -122.0, 37.0, 500, 'km', [], function ($members) use ($emit) {
                        $emit($members);
                    });
                });
            });
PHP
        );

        $this->assertSame(['sf'], $result);
    }

    public function test_georadiusbymemberro_lists_members_near_another_member(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:bge:t7:geo', function () use ($redis, $emit) {
                $redis->geoAdd('pest:bge:t7:geo', -122.4194, 37.7749, 'sf', -118.2437, 34.0522, 'la', function () use ($redis, $emit) {
                    $redis->geoRadiusByMemberRo('pest:bge:t7:geo', 'sf', 1000, 'km', [], function ($members) use ($emit) {
                        sort($members);
                        $emit($members);
                    });
                });
            });
PHP
        );

        // SF -> LA is ~560 km; both fall within 1000 km of 'sf'.
        $this->assertSame(['la', 'sf'], $result);
    }

    public function test_evalro_runs_a_read_only_lua_script(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->evalRo('return ARGV[1]', ['hello-evalro'], 0, function ($r) use ($emit) {
                $emit($r);
            });
PHP
        );

        $this->assertSame('hello-evalro', $result);
    }

    public function test_evalsharo_runs_a_read_only_lua_script_by_sha(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->script('LOAD', 'return ARGV[1]', function ($sha) use ($redis, $emit) {
                $redis->evalShaRo($sha, ['hello-shaeval'], 0, function ($r) use ($emit) {
                    $emit($r);
                });
            });
PHP
        );

        $this->assertSame('hello-shaeval', $result);
    }
}
