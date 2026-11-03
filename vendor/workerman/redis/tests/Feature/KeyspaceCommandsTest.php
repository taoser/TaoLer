<?php

/*
|--------------------------------------------------------------------------
| Keyspace commands (Group 4 §4.1)
|--------------------------------------------------------------------------
|
| Generic key-level verbs that route through Client::__call() (no explicit
| method — only @method declarations). These had no dedicated Feature
| assertion before this file:
|
|   TYPE, RENAME, RENAMENX, PERSIST, EXPIRE/TTL, PEXPIRE/PTTL,
|   EXISTS (multi-key), UNLINK, KEYS (pattern), MOVE, RANDOMKEY,
|   DUMP+RESTORE (round-trip), OBJECT ENCODING/REFCOUNT.
|
| Every key uses a pest:g4:ks:<n>: prefix to avoid collisions with the
| other workers that share db0.
|
| Engine divergences (see README Compatibility):
|   - OBJECT is unknown on Dragonfly (returns "ERR unknown command") so the
|     OBJECT cases skipOnBackend('dragonfly', ...).
*/

final class KeyspaceCommandsTest extends \Tests\RedisTestCase
{
    public function test_type_reports_the_data_type_of_a_key(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:ks:1:str', 'pest:g4:ks:1:list', function () use ($redis, $emit) {
                $redis->set('pest:g4:ks:1:str', 'v', function () use ($redis, $emit) {
                    $redis->rPush('pest:g4:ks:1:list', 'a', function () use ($redis, $emit) {
                        $redis->type('pest:g4:ks:1:str', function ($strType) use ($redis, $emit) {
                            $redis->type('pest:g4:ks:1:list', function ($listType) use ($emit, $strType) {
                                $emit(['str' => $strType, 'list' => $listType]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame('string', $result['str']);
        $this->assertSame('list', $result['list']);
    }

    public function test_rename_moves_a_value_to_a_new_key(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:ks:2:src', 'pest:g4:ks:2:dst', function () use ($redis, $emit) {
                $redis->set('pest:g4:ks:2:src', 'payload', function () use ($redis, $emit) {
                    $redis->rename('pest:g4:ks:2:src', 'pest:g4:ks:2:dst', function ($ok) use ($redis, $emit) {
                        $redis->get('pest:g4:ks:2:dst', function ($dst) use ($redis, $emit, $ok) {
                            $redis->exists('pest:g4:ks:2:src', function ($srcExists) use ($emit, $ok, $dst) {
                                $emit(['ok' => $ok, 'dst' => $dst, 'srcExists' => $srcExists]);
                            });
                        });
                    });
                });
            });
PHP
        );

        // RENAME returns +OK (decoded to true).
        $this->assertTrue($result['ok']);
        $this->assertSame('payload', $result['dst']);
        $this->assertSame(0, $result['srcExists']);
    }

    public function test_renamenx_only_renames_when_the_destination_is_absent(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:ks:3:src', 'pest:g4:ks:3:dst', function () use ($redis, $emit) {
                $redis->set('pest:g4:ks:3:src', 'a', function () use ($redis, $emit) {
                    $redis->set('pest:g4:ks:3:dst', 'b', function () use ($redis, $emit) {
                        // dst exists -> renameNx returns 0, leaves dst untouched.
                        $redis->renameNx('pest:g4:ks:3:src', 'pest:g4:ks:3:dst', function ($blocked) use ($redis, $emit) {
                            $redis->del('pest:g4:ks:3:dst', function () use ($redis, $emit, $blocked) {
                                // dst now absent -> renameNx succeeds, returns 1.
                                $redis->renameNx('pest:g4:ks:3:src', 'pest:g4:ks:3:dst', function ($ok) use ($redis, $emit, $blocked) {
                                    $redis->get('pest:g4:ks:3:dst', function ($dst) use ($emit, $blocked, $ok) {
                                        $emit(['blocked' => $blocked, 'ok' => $ok, 'dst' => $dst]);
                                    });
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(0, $result['blocked']);
        $this->assertSame(1, $result['ok']);
        $this->assertSame('a', $result['dst']);
    }

    public function test_persist_removes_an_existing_ttl(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:ks:4:k', function () use ($redis, $emit) {
                $redis->setEx('pest:g4:ks:4:k', 100, 'v', function () use ($redis, $emit) {
                    $redis->ttl('pest:g4:ks:4:k', function ($before) use ($redis, $emit) {
                        $redis->persist('pest:g4:ks:4:k', function ($persisted) use ($redis, $emit, $before) {
                            $redis->ttl('pest:g4:ks:4:k', function ($after) use ($emit, $before, $persisted) {
                                $emit(['before' => $before, 'persisted' => $persisted, 'after' => $after]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertGreaterThan(0, $result['before']);
        $this->assertSame(1, $result['persisted']);
        // -1 == key exists but has no TTL.
        $this->assertSame(-1, $result['after']);
    }

    public function test_expire_ttl_set_and_read_a_relative_ttl(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:ks:5:k', function () use ($redis, $emit) {
                $redis->set('pest:g4:ks:5:k', 'v', function () use ($redis, $emit) {
                    $redis->expire('pest:g4:ks:5:k', 50, function ($set) use ($redis, $emit) {
                        $redis->ttl('pest:g4:ks:5:k', function ($ttl) use ($emit, $set) {
                            $emit(['set' => $set, 'ttl' => $ttl]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['set']);
        $this->assertGreaterThan(0, $result['ttl']);
        $this->assertLessThanOrEqual(50, $result['ttl']);
    }

    public function test_pexpire_pttl_set_and_read_a_millisecond_ttl(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:ks:6:k', function () use ($redis, $emit) {
                $redis->set('pest:g4:ks:6:k', 'v', function () use ($redis, $emit) {
                    $redis->pexpire('pest:g4:ks:6:k', 100000, function ($set) use ($redis, $emit) {
                        $redis->pttl('pest:g4:ks:6:k', function ($pttl) use ($emit, $set) {
                            $emit(['set' => $set, 'pttl' => $pttl]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['set']);
        $this->assertGreaterThan(0, $result['pttl']);
        $this->assertLessThanOrEqual(100000, $result['pttl']);
    }

    public function test_exists_counts_each_existing_key_including_repeats(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:ks:7:a', 'pest:g4:ks:7:b', 'pest:g4:ks:7:missing', function () use ($redis, $emit) {
                $redis->set('pest:g4:ks:7:a', '1', function () use ($redis, $emit) {
                    $redis->set('pest:g4:ks:7:b', '2', function () use ($redis, $emit) {
                        // 'a' is passed twice -> counted twice. 'missing' -> 0.
                        $redis->exists('pest:g4:ks:7:a', 'pest:g4:ks:7:a', 'pest:g4:ks:7:b', 'pest:g4:ks:7:missing', function ($n) use ($emit) {
                            $emit($n);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(3, $result);
    }

    public function test_unlink_removes_keys_and_returns_the_count_freed(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:ks:8:a', 'pest:g4:ks:8:b', function () use ($redis, $emit) {
                $redis->set('pest:g4:ks:8:a', '1', function () use ($redis, $emit) {
                    $redis->set('pest:g4:ks:8:b', '2', function () use ($redis, $emit) {
                        $redis->unlink('pest:g4:ks:8:a', 'pest:g4:ks:8:b', 'pest:g4:ks:8:missing', function ($n) use ($redis, $emit) {
                            $redis->exists('pest:g4:ks:8:a', function ($stillThere) use ($emit, $n) {
                                $emit(['freed' => $n, 'stillThere' => $stillThere]);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(2, $result['freed']);
        $this->assertSame(0, $result['stillThere']);
    }

    public function test_keys_matches_a_glob_pattern(): void
    {
        $result = runInWorker(<<<'PHP'
            $prefix = 'pest:g4:ks:9:';
            $redis->del($prefix.'a', $prefix.'b', $prefix.'c', function () use ($redis, $emit, $prefix) {
                $redis->set($prefix.'a', '1', function () use ($redis, $emit, $prefix) {
                    $redis->set($prefix.'b', '2', function () use ($redis, $emit, $prefix) {
                        $redis->set($prefix.'c', '3', function () use ($redis, $emit, $prefix) {
                            $redis->keys($prefix.'*', function ($keys) use ($emit) {
                                $emit($keys);
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        sort($result);
        $this->assertSame([
            'pest:g4:ks:9:a',
            'pest:g4:ks:9:b',
            'pest:g4:ks:9:c',
        ], $result);
    }

    public function test_randomkey_returns_an_existing_key_from_a_populated_db(): void
    {
        // randomKey takes only a callback. It is in __call()'s special-case list
        // (randomKey/multi/exec/discard) so the trailing callable is popped even
        // though there is no preceding wire arg.
        $result = runInWorker(<<<'PHP'
            $redis->set('pest:g4:ks:10:seed', 'v', function () use ($redis, $emit) {
                $redis->randomKey(function ($key) use ($redis, $emit) {
                    // The returned key must actually exist in the keyspace.
                    $redis->exists($key, function ($n) use ($emit, $key) {
                        $emit(['key' => $key, 'exists' => $n]);
                    });
                });
            });
PHP
        );

        $this->assertIsString($result['key']);
        $this->assertNotSame('', $result['key']);
        $this->assertSame(1, $result['exists']);
    }

    public function test_dump_then_restore_round_trips_a_value_within_the_same_engine(): void
    {
        // DUMP returns a binary, version-stamped, CRC-checksummed serialization.
        // The exact bytes differ between Redis and Dragonfly (different RDB
        // versions), so this is a SAME-ENGINE round-trip: dump src, restore the
        // payload into dst, read dst back. The binary payload must survive the
        // RESP encoder (which sizes bulk strings by byte length, so it is
        // binary-safe).
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:ks:11:src', 'pest:g4:ks:11:dst', function () use ($redis, $emit) {
                $redis->set('pest:g4:ks:11:src', 'round-trip-value', function () use ($redis, $emit) {
                    $redis->dump('pest:g4:ks:11:src', function ($payload) use ($redis, $emit) {
                        if (!is_string($payload) || $payload === '') {
                            $emit(['error' => 'empty dump', 'payload' => $payload]);
                            return;
                        }
                        // RESTORE key ttl serialized-value  (ttl 0 = no expiry)
                        $redis->restore('pest:g4:ks:11:dst', 0, $payload, function ($ok) use ($redis, $emit) {
                            $redis->get('pest:g4:ks:11:dst', function ($value) use ($emit, $ok) {
                                $emit(['ok' => $ok, 'value' => $value]);
                            });
                        });
                    });
                });
            });
PHP
        );

        // RESTORE returns +OK -> decoded to true.
        $this->assertTrue($result['ok']);
        $this->assertSame('round-trip-value', $result['value']);
    }

    public function test_object_encoding_reports_the_internal_encoding_of_a_key(): void
    {
        // OBJECT is not implemented on Dragonfly ("ERR unknown command `OBJECT`").
        skipOnBackend('dragonfly', 'OBJECT is unknown on Dragonfly');

        $result = runInWorker(<<<'PHP'
            $redis->del('pest:g4:ks:12:k', function () use ($redis, $emit) {
                $redis->set('pest:g4:ks:12:k', 'short', function () use ($redis, $emit) {
                    $redis->object('ENCODING', 'pest:g4:ks:12:k', function ($enc) use ($redis, $emit) {
                        $redis->object('REFCOUNT', 'pest:g4:ks:12:k', function ($refcount) use ($emit, $enc) {
                            $emit(['encoding' => $enc, 'refcount' => $refcount]);
                        });
                    });
                });
            });
PHP
        );

        // A short string lands in embstr/int/raw depending on length & content;
        // 'short' is <= 44 bytes so embstr on stock Redis. Keep tolerant across
        // future Redis versions but pin the documented set.
        $this->assertContains($result['encoding'], ['embstr', 'raw', 'int']);
        $this->assertGreaterThanOrEqual(1, $result['refcount']);
    }

    public function test_move_relocates_a_key_to_another_database(): void
    {
        // MOVE key db. Use db 1 (the test default is db 0). Dragonfly and Redis
        // both support multiple logical DBs in their default config.
        $result = runInWorker(<<<'PHP'
            $redis->select(0, function () use ($redis, $emit) {
                $redis->del('pest:g4:ks:13:k', function () use ($redis, $emit) {
                    // Clear any stale copy in db1 too.
                    $redis->select(1, function () use ($redis, $emit) {
                        $redis->del('pest:g4:ks:13:k', function () use ($redis, $emit) {
                            $redis->select(0, function () use ($redis, $emit) {
                                $redis->set('pest:g4:ks:13:k', 'moved', function () use ($redis, $emit) {
                                    $redis->move('pest:g4:ks:13:k', 1, function ($moved) use ($redis, $emit) {
                                        $redis->exists('pest:g4:ks:13:k', function ($inDb0) use ($redis, $emit, $moved) {
                                            $redis->select(1, function () use ($redis, $emit, $moved, $inDb0) {
                                                $redis->get('pest:g4:ks:13:k', function ($inDb1) use ($redis, $emit, $moved, $inDb0) {
                                                    // Clean up db1, restore db0 for the shared pool.
                                                    $redis->del('pest:g4:ks:13:k', function () use ($redis, $emit, $moved, $inDb0, $inDb1) {
                                                        $redis->select(0, function () use ($emit, $moved, $inDb0, $inDb1) {
                                                            $emit(['moved' => $moved, 'inDb0' => $inDb0, 'inDb1' => $inDb1]);
                                                        });
                                                    });
                                                });
                                            });
                                        });
                                    });
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['moved']);
        $this->assertSame(0, $result['inDb0']);
        $this->assertSame('moved', $result['inDb1']);
    }
}
