<?php

use Workerman\Redis\Client;

/*
|--------------------------------------------------------------------------
| Group 9 close-out — remaining pure-logic Client branches (no server)
|--------------------------------------------------------------------------
|
| Same in-process seam as ClientCommandShapingTest: a Client built with
| newInstanceWithoutConstructor() never connects, so process() is inert and
| every command method just APPENDS [$wireArgs, time, $cb (, $format)] to
| $_queue. We read $_queue back via reflection and assert the exact wire
| array, exercise the per-method "callable in an options/positional slot"
| shortcuts, the trailing-null pop in the dotted dispatchers, the formatter
| early-return guards, and the small throw/flag side effects (xAdd empty
| message, shutdown's $_quitting). None of these need a socket, an event
| loop, or a reachable server.
|
| These are the genuinely-reachable branches the Feature integration suite
| can't hit cheaply because they are argument-massaging code paths that only
| differ in HOW the wire array is built, not in what the server replies.
*/

function t9Client(): Client
{
    return (new ReflectionClass(Client::class))->newInstanceWithoutConstructor();
}

/**
 * Invoke a Client command via a dynamic method name.
 *
 * Several command methods are overloaded through func_get_args(): the slot
 * declared `$cb = null` (or `array $options = []`) actually accepts a TTL /
 * increment / flat-options value in the "second form" (e.g.
 * set($key, $value, $ttl) -> SETEX, incr($key, $num) -> INCRBY,
 * geoRadiusRo(..., $callable) -> callback). PHPStan only sees the narrow
 * declared type and rejects passing an int / callable there. Routing those
 * second-form calls through a variable method name faithfully models the
 * dynamic-arg contract and keeps static analysis clean WITHOUT weakening the
 * assertion (we still hit the real production method).
 *
 * @param  mixed ...$args
 * @return mixed
 */
function t9Call(Client $client, string $method, ...$args)
{
    return $client->$method(...$args);
}

/**
 * @return mixed
 */
function t9Prop(Client $client, string $name)
{
    $prop = (new ReflectionClass(Client::class))->getProperty($name);
    $prop->setAccessible(true);

    return $prop->getValue($client);
}

function t9SetProp(Client $client, string $name, $value): void
{
    $prop = (new ReflectionClass(Client::class))->getProperty($name);
    $prop->setAccessible(true);
    $prop->setValue($client, $value);
}

/**
 * The wire args of the single queued entry.
 *
 * @return array<int, mixed>
 */
function t9WireArgs(Client $client): array
{
    /** @var array<int, mixed> $queue */
    $queue = t9Prop($client, '_queue');
    $entry = $queue[array_key_first($queue)];

    return (array) $entry[0];
}

/**
 * The stored callback (entry[2]) of the single queued entry.
 *
 * @return mixed
 */
function t9Cb(Client $client)
{
    /** @var array<int, mixed> $queue */
    $queue = t9Prop($client, '_queue');
    $entry = $queue[array_key_first($queue)];

    return $entry[2] ?? null;
}

/**
 * The stored format closure (entry[3]) of the single queued entry.
 *
 * @return mixed
 */
function t9Format(Client $client)
{
    /** @var array<int, mixed> $queue */
    $queue = t9Prop($client, '_queue');
    $entry = $queue[array_key_first($queue)];

    return $entry[3] ?? null;
}

final class ClientShapingTier9Test extends \Tests\TestCase
{
    // -----------------------------------------------------------------------
    // set() / incr() / decr() second-form (non-callable 3rd/2nd arg) branches
    // -----------------------------------------------------------------------

    public function test_set_key_value_ttl_routes_to_setex_with_the_ttl_in_the_time_slot(): void
    {
        $client = t9Client();
        t9Call($client, 'set', 'k', 'v', 30);

        $this->assertSame(['SETEX', 'k', 30, 'v'], t9WireArgs($client));
        $this->assertNull(t9Cb($client));
    }

    public function test_set_key_value_ttl_cb_routes_to_setex_and_keeps_the_4th_arg_callback(): void
    {
        $client = t9Client();
        $cb = function () {};
        t9Call($client, 'set', 'k', 'v', 30, $cb);

        $this->assertSame(['SETEX', 'k', 30, 'v'], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    public function test_incr_key_num_routes_to_incrby(): void
    {
        $client = t9Client();
        t9Call($client, 'incr', 'n', 5);

        $this->assertSame(['INCRBY', 'n', 5], t9WireArgs($client));
    }

    public function test_incr_key_num_cb_routes_to_incrby_and_keeps_the_callback(): void
    {
        $client = t9Client();
        $cb = function () {};
        t9Call($client, 'incr', 'n', 5, $cb);

        $this->assertSame(['INCRBY', 'n', 5], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    public function test_decr_key_num_routes_to_decrby(): void
    {
        $client = t9Client();
        t9Call($client, 'decr', 'n', 3);

        $this->assertSame(['DECRBY', 'n', 3], t9WireArgs($client));
    }

    public function test_decr_key_num_cb_routes_to_decrby_and_keeps_the_callback(): void
    {
        $client = t9Client();
        $cb = function () {};
        t9Call($client, 'decr', 'n', 3, $cb);

        $this->assertSame(['DECRBY', 'n', 3], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    // -----------------------------------------------------------------------
    // sort() / sortRo() option flattening
    // -----------------------------------------------------------------------

    public function test_sort_pulls_a_leading_sort_token_then_flattens_scalar_and_list_options(): void
    {
        $client = t9Client();
        $client->sort('mylist', [
            'sort' => 'BY weight_*',
            'LIMIT' => [0, 5],
            'ALPHA' => '',
        ]);

        // 'sort' is emitted first as a bare token, then each remaining option name
        // followed by its scalar value or each element of its list value.
        $this->assertSame(['SORT', 'mylist', 'BY weight_*', 'LIMIT', 0, 5, 'ALPHA', ''], t9WireArgs($client));
    }

    public function test_sortro_treats_a_callable_in_the_options_slot_as_the_callback(): void
    {
        $client = t9Client();
        $cb = function () {};
        $client->sortRo('mylist', $cb);

        $this->assertSame(['SORT_RO', 'mylist'], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    public function test_sortro_flattens_both_scalar_and_list_options(): void
    {
        $client = t9Client();
        $client->sortRo('mylist', ['LIMIT' => [0, 3], 'ALPHA' => '']);

        $this->assertSame(['SORT_RO', 'mylist', 'LIMIT', 0, 3, 'ALPHA', ''], t9WireArgs($client));
    }

    // -----------------------------------------------------------------------
    // xAdd() — empty-message guard + MAXLEN ~ shaping
    // -----------------------------------------------------------------------

    public function test_xadd_throws_invalidargumentexception_on_an_empty_message(): void
    {
        $client = t9Client();

        $this->assertThrows(\InvalidArgumentException::class, 'non-empty field => value message', function () use ($client) { return $client->xAdd('s', '*', []); });
        $this->assertSame([], t9Prop($client, '_queue'));
    }

    public function test_xadd_emits_maxlen_n_when_approximate_is_true_and_flattens_the_message(): void
    {
        $client = t9Client();
        $client->xAdd('s', '*', ['f' => 'v'], 100, true);

        $this->assertSame(['XADD', 's', 'MAXLEN', '~', 100, '*', 'f', 'v'], t9WireArgs($client));
    }

    public function test_xadd_folds_a_trailing_callable_in_the_maxlen_slot_as_the_callback(): void
    {
        $client = t9Client();
        $cb = function () {};
        $client->xAdd('s', '*', ['f' => 'v'], $cb);

        $this->assertSame(['XADD', 's', '*', 'f', 'v'], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    public function test_xadd_folds_a_trailing_callable_in_the_approximate_slot_as_the_callback(): void
    {
        $client = t9Client();
        $cb = function () {};
        $client->xAdd('s', '*', ['f' => 'v'], 50, $cb);

        $this->assertSame(['XADD', 's', 'MAXLEN', 50, '*', 'f', 'v'], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    public function test_xread_places_stream_names_before_their_ids(): void
    {
        $client = t9Client();
        $client->xRead(['a' => '0', 'b' => '$'], 2, 0);

        $this->assertSame(['XREAD', 'COUNT', 2, 'BLOCK', 0, 'STREAMS', 'a', 'b', '0', '$'], t9WireArgs($client));
    }

    public function test_xread_keeps_a_raw_token_list_and_a_trailing_callback(): void
    {
        $client = t9Client();
        $cb = function () {};
        $client->xRead(['COUNT', 10, 'STREAMS', 's', '0'], $cb);

        $this->assertSame(['XREAD', 'COUNT', 10, 'STREAMS', 's', '0'], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    public function test_xreadgroup_places_the_group_before_streams(): void
    {
        $client = t9Client();
        $client->xReadGroup('g', 'c', ['s' => '>'], 1);

        $this->assertSame(['XREADGROUP', 'GROUP', 'g', 'c', 'COUNT', 1, 'STREAMS', 's', '>'], t9WireArgs($client));
    }

    // -----------------------------------------------------------------------
    // hMGet() / hGetAll() formatter early-return guards (non-array passthrough)
    // -----------------------------------------------------------------------

    public function test_hmget_formatter_returns_a_non_array_reply_unchanged_and_combines_an_array_reply(): void
    {
        $client = t9Client();
        $client->hMGet('h', ['a', 'b']);
        $format = t9Format($client);
        $this->assertIsCallable($format);

        // Non-array (error string / false) passes straight through.
        $this->assertFalse($format(false));
        $this->assertSame('-ERR boom', $format('-ERR boom'));
        // Array reply is combined against the requested fields.
        $this->assertSame(['a' => '1', 'b' => '2'], $format(['1', '2']));
    }

    public function test_hgetall_formatter_returns_a_non_array_reply_unchanged_and_folds_pairs_into_a_map(): void
    {
        $client = t9Client();
        $client->hGetAll('h');
        $format = t9Format($client);
        $this->assertIsCallable($format);

        $this->assertFalse($format(false));
        $this->assertSame(['f1' => 'v1', 'f2' => 'v2'], $format(['f1', 'v1', 'f2', 'v2']));
    }

    // -----------------------------------------------------------------------
    // geo / eval read-only variants — callable-in-options-slot shortcuts
    // -----------------------------------------------------------------------

    public function test_georadiusro_takes_a_callable_options_arg_as_the_callback(): void
    {
        $client = t9Client();
        $cb = function () {};
        t9Call($client, 'geoRadiusRo', 'k', 1.0, 2.0, 100, 'm', $cb);

        $this->assertSame(['GEORADIUS_RO', 'k', 1.0, 2.0, 100, 'm'], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    public function test_georadiusbymemberro_takes_a_callable_options_arg_as_the_callback(): void
    {
        $client = t9Client();
        $cb = function () {};
        t9Call($client, 'geoRadiusByMemberRo', 'k', 'member', 100, 'm', $cb);

        $this->assertSame(['GEORADIUSBYMEMBER_RO', 'k', 'member', 100, 'm'], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    public function test_evalro_folds_a_callable_args_slot_and_a_callable_numkeys_slot_as_the_callback(): void
    {
        $clientA = t9Client();
        $cbA = function () {};
        $clientA->evalRo('return 1', $cbA);
        $this->assertSame(['EVAL_RO', 'return 1', 0], t9WireArgs($clientA));
        $this->assertSame($cbA, t9Cb($clientA));

        $clientB = t9Client();
        $cbB = function () {};
        // args given, numKeys is the callback -> numKeys defaults to count($args).
        $clientB->evalRo('return KEYS[1]', ['k1', 'k2'], $cbB);
        $this->assertSame(['EVAL_RO', 'return KEYS[1]', 2, 'k1', 'k2'], t9WireArgs($clientB));
        $this->assertSame($cbB, t9Cb($clientB));
    }

    public function test_evalsharo_folds_a_callable_args_slot_and_a_callable_numkeys_slot_as_the_callback(): void
    {
        $clientA = t9Client();
        $cbA = function () {};
        $clientA->evalShaRo('abc123', $cbA);
        $this->assertSame(['EVALSHA_RO', 'abc123', 0], t9WireArgs($clientA));
        $this->assertSame($cbA, t9Cb($clientA));

        $clientB = t9Client();
        $cbB = function () {};
        $clientB->evalShaRo('abc123', ['k1'], $cbB);
        $this->assertSame(['EVALSHA_RO', 'abc123', 1, 'k1'], t9WireArgs($clientB));
        $this->assertSame($cbB, t9Cb($clientB));
    }

    // -----------------------------------------------------------------------
    // hello() with an extra map argument
    // -----------------------------------------------------------------------

    public function test_hello_appends_an_array_extra_auth_map_after_the_protover(): void
    {
        $client = t9Client();
        $client->hello(3, ['AUTH', 'user', 'pass']);

        $this->assertSame(['HELLO', 3, ['AUTH', 'user', 'pass']], t9WireArgs($client));
    }

    // -----------------------------------------------------------------------
    // dotted dispatchers — trailing-null pop (the typed-shortcut forwarding path)
    // -----------------------------------------------------------------------

    public function test_json_bf_cms_topk_ft_drop_a_trailing_null_before_dispatching(): void
    {
        $cases = [
            ['json', 'JSON.', ['GET', 'doc', null], ['JSON.GET', 'doc']],
            ['bf', 'BF.', ['EXISTS', 'filter', 'item', null], ['BF.EXISTS', 'filter', 'item']],
            ['cms', 'CMS.', ['QUERY', 'sketch', 'item', null], ['CMS.QUERY', 'sketch', 'item']],
            ['topk', 'TOPK.', ['QUERY', 'tk', 'item', null], ['TOPK.QUERY', 'tk', 'item']],
            ['ft', 'FT.', ['INFO', 'idx', null], ['FT.INFO', 'idx']],
        ];
        foreach ($cases as [$method, $_prefix, $args, $expected]) {
            $client = t9Client();
            $client->{$method}(...$args);
            $this->assertSame($expected, t9WireArgs($client));
            // The trailing null was popped, so there is no stored callback.
            $this->assertNull(t9Cb($client));
        }
    }

    // -----------------------------------------------------------------------
    // json* typed shortcuts — callable-in-path-slot
    // -----------------------------------------------------------------------

    public function test_json_typed_getters_take_a_callable_path_arg_as_the_callback_and_default_path_to(): void
    {
        $methods = ['jsonType', 'jsonObjKeys', 'jsonObjLen', 'jsonArrLen', 'jsonStrLen', 'jsonDel', 'jsonForget'];
        $expectedVerb = [
            'jsonType' => 'JSON.TYPE',
            'jsonObjKeys' => 'JSON.OBJKEYS',
            'jsonObjLen' => 'JSON.OBJLEN',
            'jsonArrLen' => 'JSON.ARRLEN',
            'jsonStrLen' => 'JSON.STRLEN',
            'jsonDel' => 'JSON.DEL',
            'jsonForget' => 'JSON.FORGET',
        ];
        foreach ($methods as $method) {
            $client = t9Client();
            $cb = function () {};
            $client->{$method}('doc', $cb);
            $this->assertSame([$expectedVerb[$method], 'doc', '$'], t9WireArgs($client));
            $this->assertSame($cb, t9Cb($client));
        }
    }

    public function test_jsonmget_takes_a_callable_path_arg_as_the_callback_and_flattens_keys(): void
    {
        $client = t9Client();
        $cb = function () {};
        $client->jsonMGet(['d1', 'd2'], $cb);

        $this->assertSame(['JSON.MGET', 'd1', 'd2', '$'], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    // -----------------------------------------------------------------------
    // cmsMerge() — callable-weights shortcut + WEIGHTS branch
    // -----------------------------------------------------------------------

    public function test_cmsmerge_without_weights_builds_merge_dest_numkeys_src(): void
    {
        $client = t9Client();
        $client->cmsMerge('dest', 2, ['a', 'b']);

        $this->assertSame(['CMS.MERGE', 'dest', 2, 'a', 'b'], t9WireArgs($client));
    }

    public function test_cmsmerge_appends_a_weights_clause_when_weights_are_given(): void
    {
        $client = t9Client();
        $client->cmsMerge('dest', 2, ['a', 'b'], [3, 4]);

        $this->assertSame(['CMS.MERGE', 'dest', 2, 'a', 'b', 'WEIGHTS', 3, 4], t9WireArgs($client));
    }

    // -----------------------------------------------------------------------
    // ftDropIndex() — DD branch + callable-deleteDocs shortcut
    // -----------------------------------------------------------------------

    public function test_ftdropindex_without_dd_omits_the_dd_token(): void
    {
        $client = t9Client();
        $client->ftDropIndex('idx');

        $this->assertSame(['FT.DROPINDEX', 'idx'], t9WireArgs($client));
    }

    public function test_ftdropindex_with_deletedocs_appends_dd(): void
    {
        $client = t9Client();
        $client->ftDropIndex('idx', true);

        $this->assertSame(['FT.DROPINDEX', 'idx', 'DD'], t9WireArgs($client));
    }

    public function test_ftdropindex_folds_a_callable_deletedocs_arg_as_the_callback_no_dd(): void
    {
        $client = t9Client();
        $cb = function () {};
        $client->ftDropIndex('idx', $cb);

        $this->assertSame(['FT.DROPINDEX', 'idx'], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    // -----------------------------------------------------------------------
    // shutdown() — mode shaping, callable-mode shortcut, and the _quitting flag
    // -----------------------------------------------------------------------

    public function test_shutdown_defaults_to_save_and_sets_the_quitting_flag(): void
    {
        $client = t9Client();
        t9SetProp($client, '_quitting', false);
        $client->shutdown();

        $this->assertSame(['SHUTDOWN', 'SAVE'], t9WireArgs($client));
        $this->assertTrue(t9Prop($client, '_quitting'));
    }

    public function test_shutdown_with_nosave_shapes_the_mode_token(): void
    {
        $client = t9Client();
        $client->shutdown('NOSAVE');

        $this->assertSame(['SHUTDOWN', 'NOSAVE'], t9WireArgs($client));
    }

    public function test_shutdown_folds_a_callable_mode_arg_as_the_callback_and_keeps_save(): void
    {
        $client = t9Client();
        $cb = function () {};
        $client->shutdown($cb);

        $this->assertSame(['SHUTDOWN', 'SAVE'], t9WireArgs($client));
        $this->assertSame($cb, t9Cb($client));
    }

    // -----------------------------------------------------------------------
    // monitor() — pending-stream early return + rejection handler
    // -----------------------------------------------------------------------

    public function test_monitor_is_a_no_op_when_a_stream_is_already_pending_in_the_queue(): void
    {
        $client = t9Client();
        // A SUBSCRIBE already sitting in the queue makes streamActiveOrPending()
        // true even though the _subscribe flag is still false (never sent).
        t9SetProp($client, '_queue', [[['SUBSCRIBE', 'chan'], time(), function () {}]]);

        $client->monitor(function () {});

        // No MONITOR entry was appended — the queue is unchanged (length 1).
        $this->assertCount(1, t9Prop($client, '_queue'));
        $this->assertSame(['SUBSCRIBE', 'chan'], t9WireArgs($client));
    }

    public function test_monitor_rejection_handler_clears_the_lock_drops_the_pinned_entry_and_reports_false(): void
    {
        $client = t9Client();
        // Queue a MONITOR entry as process() would have, and set the lock.
        $client->monitor(function ($result) use (&$reported) {
            $reported = $result;
        });
        // Grab the wrapper callback the client stored, then simulate the lock
        // process() would set and feed it a rejection (false).
        $reported = 'untouched';
        $wrapper = t9Cb($client);
        t9SetProp($client, '_monitoring', true);
        // The queued head is the MONITOR entry; the handler should unset it.
        $wrapper(false);

        $this->assertFalse($reported);
        $this->assertFalse(t9Prop($client, '_monitoring'));
        $this->assertSame([], t9Prop($client, '_queue'));
    }

    public function test_monitor_wrapper_swallows_the_ok_handshake_true_without_invoking_the_user_cb(): void
    {
        $client = t9Client();
        $seen = 'untouched';
        $client->monitor(function ($line) use (&$seen) {
            $seen = $line;
        });
        $wrapper = t9Cb($client);

        // true is the MONITOR +OK handshake — swallowed.
        $wrapper(true);
        $this->assertSame('untouched', $seen);

        // A real monitor line is forwarded.
        $wrapper('1700000000.0 [0 127.0.0.1:1] "PING"');
        $this->assertSame('1700000000.0 [0 127.0.0.1:1] "PING"', $seen);
    }
}
