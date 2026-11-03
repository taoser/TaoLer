<?php

/*
|--------------------------------------------------------------------------
| RedisJSON / JSON.* module
|--------------------------------------------------------------------------
|
| Dragonfly natively implements the RedisJSON command set. These tests
| exercise the json() dispatcher and the typed shortcuts (jsonSet,
| jsonGet, jsonArrAppend, …) added to Client.php.
|
| Each test uses a unique pest:json:tN: prefix so concurrent runs don't
| collide. JSON values go on the wire as JSON-encoded strings; the
| format-callback layer does not decode them, so assertions json_decode
| the reply where they need a PHP array.
*/

final class JsonTest extends \Tests\RedisTestCase
{
    public function test_json_dispatcher_works_for_arbitrary_subcommand(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t1:doc', function () use ($redis, $emit) {
                // SET via the raw dispatcher (no shortcut method).
                $redis->json('SET', 'pest:json:t1:doc', '$', '{"hello":"world"}', function ($ok) use ($redis, $emit) {
                    $redis->json('GET', 'pest:json:t1:doc', function ($reply) use ($ok, $emit) {
                        $emit(['ok' => $ok, 'doc' => $reply]);
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertTrue($result['ok']);
        $this->assertSame(['hello' => 'world'], json_decode($result['doc'], true));
    }

    public function test_jsonset_and_jsonget_round_trip_a_document(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t2:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t2:doc', '$', '{"name":"alice","age":30}', function ($ok) use ($redis, $emit) {
                    $redis->jsonGet('pest:json:t2:doc', function ($reply) use ($ok, $emit) {
                        $emit(['ok' => $ok, 'doc' => $reply]);
                    });
                });
            });
PHP
        );

        $this->assertTrue($result['ok']);
        // Dragonfly serialises object keys alphabetically, so compare unordered.
        $this->assertEqualsCanonicalizing(['name' => 'alice', 'age' => 30], json_decode($result['doc'], true));
    }

    public function test_jsonget_with_multiple_paths_returns_a_path_keyed_object(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t3:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t3:doc', '$', '{"a":1,"b":"hi","c":[1,2,3]}', function () use ($redis, $emit) {
                    $redis->jsonGet('pest:json:t3:doc', '$.a', '$.b', function ($reply) use ($emit) {
                        $emit(['doc' => $reply]);
                    });
                });
            });
PHP
        );

        $decoded = json_decode($result['doc'], true);
        $this->assertIsArray($decoded);
        // Dragonfly returns multi-path GET as {"$.a":[1],"$.b":["hi"]}.
        $this->assertArrayHasKey('$.a', $decoded);
        $this->assertArrayHasKey('$.b', $decoded);
        $this->assertSame([1], $decoded['$.a']);
        $this->assertSame(['hi'], $decoded['$.b']);
    }

    public function test_jsondel_removes_a_path(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t4:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t4:doc', '$', '{"keep":1,"drop":2}', function () use ($redis, $emit) {
                    $redis->jsonDel('pest:json:t4:doc', '$.drop', function ($removed) use ($redis, $emit) {
                        $redis->jsonGet('pest:json:t4:doc', function ($reply) use ($removed, $emit) {
                            $emit(['removed' => $removed, 'doc' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['removed']);
        $this->assertSame(['keep' => 1], json_decode($result['doc'], true));
    }

    public function test_jsonforget_removes_a_path_alias_of_del(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t5:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t5:doc', '$', '{"keep":1,"drop":2}', function () use ($redis, $emit) {
                    $redis->jsonForget('pest:json:t5:doc', '$.drop', function ($removed) use ($redis, $emit) {
                        $redis->jsonGet('pest:json:t5:doc', function ($reply) use ($removed, $emit) {
                            $emit(['removed' => $removed, 'doc' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(1, $result['removed']);
        $this->assertSame(['keep' => 1], json_decode($result['doc'], true));
    }

    public function test_jsonmget_returns_aligned_values_across_multiple_keys(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t6:a', 'pest:json:t6:b', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t6:a', '$', '{"v":1}', function () use ($redis, $emit) {
                    $redis->jsonSet('pest:json:t6:b', '$', '{"v":2}', function () use ($redis, $emit) {
                        $redis->jsonMGet(['pest:json:t6:a', 'pest:json:t6:b'], '$.v', function ($reply) use ($emit) {
                            $emit(['reply' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result['reply']);
        $this->assertCount(2, $result['reply']);
        $this->assertSame([1], json_decode($result['reply'][0], true));
        $this->assertSame([2], json_decode($result['reply'][1], true));
    }

    public function test_jsonmset_stores_multiple_docs_atomically(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t7:a', 'pest:json:t7:b', function () use ($redis, $emit) {
                $tuples = [
                    ['pest:json:t7:a', '$', '{"q":1}'],
                    ['pest:json:t7:b', '$', '{"q":2}'],
                ];
                $redis->jsonMSet($tuples, function ($ok) use ($redis, $emit) {
                    $redis->jsonGet('pest:json:t7:a', function ($a) use ($redis, $ok, $emit) {
                        $redis->jsonGet('pest:json:t7:b', function ($b) use ($ok, $a, $emit) {
                            $emit(['ok' => $ok, 'a' => $a, 'b' => $b]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertTrue($result['ok']);
        $this->assertSame(['q' => 1], json_decode($result['a'], true));
        $this->assertSame(['q' => 2], json_decode($result['b'], true));
    }

    public function test_jsonmerge_merges_into_an_existing_doc(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t8:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t8:doc', '$', '{"a":1,"b":2}', function () use ($redis, $emit) {
                    $redis->jsonMerge('pest:json:t8:doc', '$', '{"b":99,"c":3}', function ($ok) use ($redis, $emit) {
                        $redis->jsonGet('pest:json:t8:doc', function ($reply) use ($ok, $emit) {
                            $emit(['ok' => $ok, 'doc' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertTrue($result['ok']);
        $decoded = json_decode($result['doc'], true);
        $this->assertSame(['a' => 1, 'b' => 99, 'c' => 3], array_intersect_key($decoded, ['a' => 1, 'b' => 99, 'c' => 3]));
    }

    public function test_jsonarrappend_appends_to_an_array_element(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t9:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t9:doc', '$', '{"tags":["a","b"]}', function () use ($redis, $emit) {
                    $redis->jsonArrAppend('pest:json:t9:doc', '$.tags', '"c"', '"d"', function ($lengths) use ($redis, $emit) {
                        $redis->jsonGet('pest:json:t9:doc', '$.tags', function ($reply) use ($lengths, $emit) {
                            $emit(['lengths' => $lengths, 'tags' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        // jsonArrAppend returns an array of new lengths per matched path.
        $this->assertSame([4], $result['lengths']);
        $this->assertSame([['a', 'b', 'c', 'd']], json_decode($result['tags'], true));
    }

    public function test_jsonarrlen_returns_array_length(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t10:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t10:doc', '$', '{"xs":[1,2,3,4,5]}', function () use ($redis, $emit) {
                    $redis->jsonArrLen('pest:json:t10:doc', '$.xs', function ($reply) use ($emit) {
                        $emit(['len' => $reply]);
                    });
                });
            });
PHP
        );

        $this->assertSame([5], $result['len']);
    }

    public function test_jsonobjkeys_lists_object_keys(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t11:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t11:doc', '$', '{"a":1,"b":2,"c":3}', function () use ($redis, $emit) {
                    $redis->jsonObjKeys('pest:json:t11:doc', '$', function ($reply) use ($emit) {
                        $emit(['keys' => $reply]);
                    });
                });
            });
PHP
        );

        // OBJKEYS over a JSONPath returns an array of arrays (one per match).
        $this->assertIsArray($result['keys']);
        $this->assertCount(1, $result['keys']);
        $keys = $result['keys'][0];
        sort($keys);
        $this->assertSame(['a', 'b', 'c'], $keys);
    }

    public function test_jsonobjlen_returns_object_key_count(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t12:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t12:doc', '$', '{"a":1,"b":2,"c":3,"d":4}', function () use ($redis, $emit) {
                    $redis->jsonObjLen('pest:json:t12:doc', '$', function ($reply) use ($emit) {
                        $emit(['len' => $reply]);
                    });
                });
            });
PHP
        );

        $this->assertSame([4], $result['len']);
    }

    public function test_jsontype_returns_the_json_type(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t13:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t13:doc', '$', '{"n":42,"s":"hi","arr":[1,2]}', function () use ($redis, $emit) {
                    $redis->jsonType('pest:json:t13:doc', '$.n', function ($numType) use ($redis, $emit) {
                        $redis->jsonType('pest:json:t13:doc', '$.s', function ($strType) use ($redis, $numType, $emit) {
                            $redis->jsonType('pest:json:t13:doc', '$.arr', function ($arrType) use ($numType, $strType, $emit) {
                                $emit(['n' => $numType, 's' => $strType, 'arr' => $arrType]);
                            });
                        });
                    });
                });
            });
PHP
        );

        // JSONPath form returns an array of type names, one per match.
        $this->assertSame(['integer'], $result['n']);
        $this->assertSame(['string'], $result['s']);
        $this->assertSame(['array'], $result['arr']);
    }

    public function test_jsonnumincrby_increments_a_number(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t14:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t14:doc', '$', '{"n":10}', function () use ($redis, $emit) {
                    $redis->jsonNumIncrBy('pest:json:t14:doc', '$.n', 7, function ($reply) use ($redis, $emit) {
                        $redis->jsonGet('pest:json:t14:doc', '$.n', function ($doc) use ($reply, $emit) {
                            $emit(['inc' => $reply, 'final' => $doc]);
                        });
                    });
                });
            });
PHP
        );

        // The increment reply is a JSON-encoded array of new values.
        $this->assertSame([17], json_decode($result['inc'], true));
        $this->assertSame([17], json_decode($result['final'], true));
    }

    public function test_jsonstrappend_appends_to_a_string(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t15:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t15:doc', '$', '{"s":"hi"}', function () use ($redis, $emit) {
                    // RedisJSON STRAPPEND expects a JSON-encoded string literal.
                    $redis->jsonStrAppend('pest:json:t15:doc', '$.s', '"!"', function ($lengths) use ($redis, $emit) {
                        $redis->jsonGet('pest:json:t15:doc', '$.s', function ($reply) use ($lengths, $emit) {
                            $emit(['lengths' => $lengths, 's' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame([3], $result['lengths']);
        $this->assertSame(['hi!'], json_decode($result['s'], true));
    }

    public function test_jsonstrlen_returns_string_length(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t16:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t16:doc', '$', '{"s":"hello"}', function () use ($redis, $emit) {
                    $redis->jsonStrLen('pest:json:t16:doc', '$.s', function ($reply) use ($emit) {
                        $emit(['len' => $reply]);
                    });
                });
            });
PHP
        );

        $this->assertSame([5], $result['len']);
    }

    public function test_jsontoggle_toggles_a_boolean(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->del('pest:json:t17:doc', function () use ($redis, $emit) {
                $redis->jsonSet('pest:json:t17:doc', '$', '{"flag":true}', function () use ($redis, $emit) {
                    $redis->jsonToggle('pest:json:t17:doc', '$.flag', function ($toggleReply) use ($redis, $emit) {
                        $redis->jsonGet('pest:json:t17:doc', '$.flag', function ($reply) use ($toggleReply, $emit) {
                            $emit(['toggle' => $toggleReply, 'flag' => $reply]);
                        });
                    });
                });
            });
PHP
        );

        // After toggling true -> false. Toggle reply is an array of new states (0/1).
        $this->assertSame([0], $result['toggle']);
        $this->assertSame([false], json_decode($result['flag'], true));
    }
}
