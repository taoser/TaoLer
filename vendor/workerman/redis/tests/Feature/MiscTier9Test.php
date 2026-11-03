<?php

/*
|--------------------------------------------------------------------------
| Tier 9 — miscellaneous explicit methods
|--------------------------------------------------------------------------
|
| Covers the three explicit methods added in this tier that don't fall
| under a module:
|
|   - bgSave()      — BGSAVE no-arg-callback fix.
|   - moduleList()  — MODULE LIST via the module() dispatcher.
|   - sortRo()      — SORT_RO with the underscore preserved on the wire.
|
| Each test uses a unique pest:* prefix so concurrent runs don't collide.
*/

final class MiscTier9Test extends \Tests\RedisTestCase
{
    public function test_bgsave_triggers_a_background_snapshot(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->bgSave(function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        // Dragonfly returns +OK (decoded to true). Stock Redis returns the
        // status bulk 'Background saving started'. Accept either.
        $this->assertTrue($result === true || (\is_string($result) && \str_contains($result, 'aving')));
    }

    public function test_modulelist_returns_the_loaded_modules(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->moduleList(function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        $this->assertIsArray($result);
        // Reply on Dragonfly is a flat list of name/value pairs per module.
        // We just confirm at least one named module is reported — flatten and
        // search for the 'name' key.
        $flat = [];
        array_walk_recursive($result, function ($v) use (&$flat) { $flat[] = $v; });
        $this->assertContains('name', $flat);
    }

    public function test_sortro_with_alpha_sorts_a_list_of_strings(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:sortro:t1:list';
            $redis->del($key, function () use ($redis, $emit, $key) {
                // LPUSH inserts at head, so the resulting order is c,b,a.
                $redis->lPush($key, 'c', function () use ($redis, $emit, $key) {
                    $redis->lPush($key, 'a', function () use ($redis, $emit, $key) {
                        $redis->lPush($key, 'b', function () use ($redis, $emit, $key) {
                            // ALPHA is a flag-only option; the value is sent
                            // alongside the key on the wire but Dragonfly
                            // tolerates an empty placeholder. Use an indexed
                            // entry where the key IS the flag and the value
                            // is the same string so the wire reads
                            // "SORT_RO key ALPHA ALPHA"; the server accepts
                            // the duplicate ALPHA token as part of the flag.
                            $redis->sortRo($key, ['ALPHA' => 'ALPHA'], function ($reply) use ($redis, $emit, $key) {
                                $redis->del($key, function () use ($emit, $reply) {
                                    $emit($reply);
                                });
                            });
                        });
                    });
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame(['a', 'b', 'c'], $result);
    }

    public function test_sortro_with_limit_slices_the_result_window(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:sortro:t2:list';
            $redis->del($key, function () use ($redis, $emit, $key) {
                $redis->lPush($key, '5', function () use ($redis, $emit, $key) {
                    $redis->lPush($key, '3', function () use ($redis, $emit, $key) {
                        $redis->lPush($key, '1', function () use ($redis, $emit, $key) {
                            $redis->lPush($key, '4', function () use ($redis, $emit, $key) {
                                $redis->lPush($key, '2', function () use ($redis, $emit, $key) {
                                    // LIMIT takes two args; pass them as a list
                                    // so sortRo() flattens them onto the wire
                                    // as `LIMIT 1 2`.
                                    $redis->sortRo($key, ['LIMIT' => [1, 2]], function ($reply) use ($redis, $emit, $key) {
                                        $redis->del($key, function () use ($emit, $reply) {
                                            $emit($reply);
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

        $this->assertIsArray($result);
        // Numeric sort yields [1,2,3,4,5]; LIMIT 1 2 takes 2 elements starting
        // at offset 1, so [2,3].
        $this->assertSame(['2', '3'], $result);
    }
}
