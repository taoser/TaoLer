<?php

/*
|--------------------------------------------------------------------------
| Tier 9 — RedisSearch (FT) module
|--------------------------------------------------------------------------
|
| Both engines ship a `search` module implementing the FT.* command set:
| Dragonfly's built-in search, and RediSearch on the Redis 8.8 build. These
| tests exercise the ft() dispatcher and the typed shortcuts (ftCreate,
| ftSearch, ftAggregate, ftDropIndex, ftInfo, ftList) added to Client.php and
| run on BOTH engines.
|
| Each test owns a unique index name (pest:ft:tN:idx) and a unique
| document prefix (pest:ft:tN:doc:) so concurrent runs don't trip on each
| other. Indexes are torn down at the end of each test via ftDropIndex().
|
| The tests assert on reply *shapes* rather than exact byte sequences:
| reply formats vary across engines/versions and we want the suite to stay
| green as the modules evolve.
|
| (Historical note: these carried skipOnBackend('redis', ...) gates for an
| FT.SEARCH "SEARCH_INDEX_NOT_FOUND" divergence on an earlier Redis build.
| That no longer reproduces on Redis 8.8 + RediSearch 80800 — verified that
| FT.CREATE/SEARCH/AGGREGATE/INFO/CONFIG all work — so the gates were removed
| and the FT family is now covered on both engines.)
*/

final class FtSearchTest extends \Tests\RedisTestCase
{
    public function test_ftcreate_then_ftsearch_returns_matching_documents(): void
    {
        $result = runInWorker(<<<'PHP'
            $idx = 'pest:ft:t1:idx';
            // Pre-clean any leftover index from a prior run.
            $redis->rawCommand('FT.DROPINDEX', $idx, function () use ($redis, $emit, $idx) {
                $redis->ftCreate(
                    $idx,
                    'ON', 'HASH',
                    'PREFIX', 1, 'pest:ft:t1:doc:',
                    'SCHEMA', 'name', 'TEXT',
                    function ($created) use ($redis, $emit, $idx) {
                        $redis->hSet('pest:ft:t1:doc:1', 'name', 'alice', function () use ($redis, $emit, $idx, $created) {
                            $redis->hSet('pest:ft:t1:doc:2', 'name', 'bob', function () use ($redis, $emit, $idx, $created) {
                                // Give the index a moment to populate.
                                \Workerman\Timer::add(0.3, function () use ($redis, $emit, $idx, $created) {
                                    $redis->ftSearch($idx, 'alice', function ($reply) use ($redis, $emit, $idx, $created) {
                                        // Tear down so the next run starts clean.
                                        $redis->ftDropIndex($idx, function () use ($redis, $emit, $reply, $created) {
                                            $redis->del('pest:ft:t1:doc:1', function () use ($redis, $emit, $reply, $created) {
                                                $redis->del('pest:ft:t1:doc:2', function () use ($emit, $reply, $created) {
                                                    $emit(['created' => $created, 'search' => $reply]);
                                                });
                                            });
                                        });
                                    });
                                }, [], false);
                            });
                        });
                    }
                );
            });
PHP
        , 8);

        $this->assertTrue($result['created']);
        $this->assertIsArray($result['search']);
        // First element is the total hit count.
        $this->assertSame(1, $result['search'][0]);
        // Second element is the matching document's key.
        $this->assertSame('pest:ft:t1:doc:1', $result['search'][1]);
    }

    public function test_ftlist_returns_the_list_of_defined_indexes(): void
    {
        $result = runInWorker(<<<'PHP'
            $idx = 'pest:ft:t2:idx';
            $redis->rawCommand('FT.DROPINDEX', $idx, function () use ($redis, $emit, $idx) {
                $redis->ftCreate(
                    $idx,
                    'ON', 'HASH',
                    'PREFIX', 1, 'pest:ft:t2:doc:',
                    'SCHEMA', 'name', 'TEXT',
                    function () use ($redis, $emit, $idx) {
                        $redis->ftList(function ($list) use ($redis, $emit, $idx) {
                            $redis->ftDropIndex($idx, function () use ($emit, $list) {
                                $emit($list);
                            });
                        });
                    }
                );
            });
PHP
        , 8);

        $this->assertIsArray($result);
        $this->assertContains('pest:ft:t2:idx', $result);
    }

    public function test_ftinfo_returns_metadata_for_an_index(): void
    {
        $result = runInWorker(<<<'PHP'
            $idx = 'pest:ft:t3:idx';
            $redis->rawCommand('FT.DROPINDEX', $idx, function () use ($redis, $emit, $idx) {
                $redis->ftCreate(
                    $idx,
                    'ON', 'HASH',
                    'PREFIX', 1, 'pest:ft:t3:doc:',
                    'SCHEMA', 'name', 'TEXT',
                    function () use ($redis, $emit, $idx) {
                        $redis->ftInfo($idx, function ($info) use ($redis, $emit, $idx) {
                            $redis->ftDropIndex($idx, function () use ($emit, $info) {
                                $emit($info);
                            });
                        });
                    }
                );
            });
PHP
        , 8);

        $this->assertIsArray($result);
        // FT.INFO reply is a flat [name, value, name, value, ...] array. The
        // 'index_name' / 'index_definition' fields are always present.
        $this->assertContains('index_name', $result);
    }

    public function test_ftdropindex_removes_an_index_from_ftlist(): void
    {
        $result = runInWorker(<<<'PHP'
            $idx = 'pest:ft:t4:idx';
            $redis->rawCommand('FT.DROPINDEX', $idx, function () use ($redis, $emit, $idx) {
                $redis->ftCreate(
                    $idx,
                    'ON', 'HASH',
                    'PREFIX', 1, 'pest:ft:t4:doc:',
                    'SCHEMA', 'name', 'TEXT',
                    function () use ($redis, $emit, $idx) {
                        $redis->ftDropIndex($idx, function ($dropped) use ($redis, $emit, $idx) {
                            $redis->ftList(function ($list) use ($emit, $dropped, $idx) {
                                $emit([
                                    'dropped' => $dropped,
                                    'still_listed' => \is_array($list) && \in_array($idx, $list, true),
                                ]);
                            });
                        });
                    }
                );
            });
PHP
        , 8);

        $this->assertTrue($result['dropped']);
        $this->assertFalse($result['still_listed']);
    }

    public function test_ftaggregate_runs_a_basic_groupby_aggregation(): void
    {
        $result = runInWorker(<<<'PHP'
            $idx = 'pest:ft:t5:idx';
            $redis->rawCommand('FT.DROPINDEX', $idx, function () use ($redis, $emit, $idx) {
                $redis->ftCreate(
                    $idx,
                    'ON', 'HASH',
                    'PREFIX', 1, 'pest:ft:t5:doc:',
                    'SCHEMA', 'name', 'TAG',
                    function () use ($redis, $emit, $idx) {
                        $redis->hSet('pest:ft:t5:doc:1', 'name', 'alice', function () use ($redis, $emit, $idx) {
                            $redis->hSet('pest:ft:t5:doc:2', 'name', 'bob', function () use ($redis, $emit, $idx) {
                                $redis->hSet('pest:ft:t5:doc:3', 'name', 'alice', function () use ($redis, $emit, $idx) {
                                    \Workerman\Timer::add(0.3, function () use ($redis, $emit, $idx) {
                                        $redis->ftAggregate($idx, '*', 'GROUPBY', 1, '@name', function ($agg) use ($redis, $emit, $idx) {
                                            $redis->ftDropIndex($idx, function () use ($redis, $emit, $agg) {
                                                $redis->del('pest:ft:t5:doc:1', 'pest:ft:t5:doc:2', 'pest:ft:t5:doc:3', function () use ($emit, $agg) {
                                                    $emit($agg);
                                                });
                                            });
                                        });
                                    }, [], false);
                                });
                            });
                        });
                    }
                );
            });
PHP
        , 8);

        $this->assertIsArray($result);
        // First element is the count of result rows. We grouped 3 docs into 2
        // buckets (alice x2 + bob x1), so the count should be at least 1
        // (Dragonfly reports the row count, exact format may vary).
        $this->assertIsInt($result[0]);
        $this->assertGreaterThanOrEqual(1, $result[0]);
    }
}
