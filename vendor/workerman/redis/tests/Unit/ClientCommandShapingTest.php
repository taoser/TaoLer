<?php

use Workerman\Redis\Client;

/*
|--------------------------------------------------------------------------
| Client command-shaping (pure logic, no event loop, no server)
|--------------------------------------------------------------------------
|
| These tests drive Client's argument-shaping / queueing decisions WITHOUT
| a socket or the Workerman event loop. The seam:
|
|   - A Client built with newInstanceWithoutConstructor() never runs the
|     constructor, so connect() is never called and $_connection stays null
|     and no Timer is registered.
|   - queueCommand() -> process() is a guaranteed no-op while $_connection
|     is null (process() returns immediately), so every command method just
|     APPENDS [$wireArgs, time, $cb (, $format)] to $_queue. We then read
|     $_queue back via reflection and assert the exact wire array + whether a
|     trailing callable was popped as the callback.
|
| Bound to Tests\TestCase (Pest binds Unit/ automatically) so this passes
| with no server reachable — that is the point of the tier.
*/

/**
 * Build a Client without running its constructor (no connect(), no Timer,
 * $_connection === null so process() is inert).
 */
function shapingClient(): Client
{
    return (new ReflectionClass(Client::class))->newInstanceWithoutConstructor();
}

/**
 * Read a protected/private property off a Client instance.
 *
 * @return mixed
 */
function shapingProp(Client $client, string $name)
{
    $prop = (new ReflectionClass(Client::class))->getProperty($name);
    $prop->setAccessible(true);

    return $prop->getValue($client);
}

/**
 * Return the queued command entries as a list of [wireArgs, callbackOrNull].
 * Drops the timestamp and any format closure so assertions stay stable.
 *
 * @return array<int, array{0: array<int, mixed>, 1: callable|null}>
 */
function shapingQueue(Client $client): array
{
    $out = [];
    foreach (shapingProp($client, '_queue') as $entry) {
        $out[] = [$entry[0], $entry[2] ?? null];
    }

    return $out;
}

final class ClientCommandShapingTest extends \Tests\TestCase
{
    // -----------------------------------------------------------------------
    // __call() trailing-callable popping
    // -----------------------------------------------------------------------

    public function test_call_uppercases_the_verb_and_prepends_it_to_the_args(): void
    {
        $client = shapingClient();
        $client->get('mykey');

        $queue = shapingQueue($client);
        $this->assertCount(1, $queue);
        $this->assertSame(['GET', 'mykey'], $queue[0][0]);
        $this->assertNull($queue[0][1]);
    }

    public function test_call_pops_a_trailing_callable_as_the_callback_when_count_args_1(): void
    {
        $client = shapingClient();
        $cb = function () {};
        $client->get('mykey', $cb);

        $queue = shapingQueue($client);
        $this->assertSame(['GET', 'mykey'], $queue[0][0]);
        $this->assertSame($cb, $queue[0][1]);
    }

    public function test_call_does_not_treat_a_lone_callable_arg_as_a_callback_the_documented_footgun(): void
    {
        // count($args) === 1 and the method is not in the exception list, so the
        // callable is sent as a literal command ARG, not popped as the callback.
        $client = shapingClient();
        $cb = function () {};
        $client->get($cb);

        $queue = shapingQueue($client);
        $this->assertCount(2, $queue[0][0]);
        $this->assertSame('GET', $queue[0][0][0]);
        $this->assertSame($cb, $queue[0][0][1]);     // the callable rode along as an arg
        $this->assertNull($queue[0][1]);        // and was NOT taken as the callback
    }

    public function test_call_pops_a_lone_callable_for_the_exception_list_verbs_randomkey_multi_exec_discard(): void
    {
        foreach (['randomKey', 'multi', 'exec', 'discard'] as $verb) {
            $client = shapingClient();
            $cb = function () {};
            $client->{$verb}($cb);

            $queue = shapingQueue($client);
            // Only the uppercased verb makes it to the wire; the callable is popped.
            $this->assertSame([strtoupper($verb)], $queue[0][0]);
            $this->assertSame($cb, $queue[0][1]);
        }
    }

    public function test_call_keeps_a_non_callable_last_arg_in_place_even_with_count_1(): void
    {
        // lPush routes through __call (no concrete method). With three args and a
        // non-callable tail, nothing is popped as a callback.
        $client = shapingClient();
        $client->lPush('list', 'a', 'b');

        $queue = shapingQueue($client);
        $this->assertSame(['LPUSH', 'list', 'a', 'b'], $queue[0][0]);
        $this->assertNull($queue[0][1]);
    }

    // -----------------------------------------------------------------------
    // dispatcher() prefix styles
    // -----------------------------------------------------------------------

    public function test_dispatcher_glues_the_verb_onto_a_dot_prefixed_family_json_set(): void
    {
        $client = shapingClient();
        $client->json('set', 'doc', '$', '{"a":1}');

        $queue = shapingQueue($client);
        $this->assertSame(['JSON.SET', 'doc', '$', '{"a":1}'], $queue[0][0]);
        $this->assertNull($queue[0][1]);
    }

    public function test_dispatcher_splits_a_space_prefixed_family_into_two_wire_tokens_config_get(): void
    {
        $client = shapingClient();
        $client->config('get', 'maxmemory');

        $queue = shapingQueue($client);
        $this->assertSame(['CONFIG', 'GET', 'maxmemory'], $queue[0][0]);
        $this->assertNull($queue[0][1]);
    }

    public function test_dispatcher_pops_a_trailing_callable_space_prefix_and_uppercases_the_verb(): void
    {
        $client = shapingClient();
        $cb = function () {};
        $client->cluster('info', $cb);

        $queue = shapingQueue($client);
        $this->assertSame(['CLUSTER', 'INFO'], $queue[0][0]);
        $this->assertSame($cb, $queue[0][1]);
    }

    public function test_dispatcher_pops_a_trailing_callable_dot_prefix(): void
    {
        $client = shapingClient();
        $cb = function () {};
        $client->json('get', 'doc', $cb);

        $queue = shapingQueue($client);
        $this->assertSame(['JSON.GET', 'doc'], $queue[0][0]);
        $this->assertSame($cb, $queue[0][1]);
    }

    public function test_dispatcher_uppercases_a_lower_case_verb_for_the_dot_family(): void
    {
        $client = shapingClient();
        $client->bf('add', 'filter', 'item');

        $queue = shapingQueue($client);
        $this->assertSame(['BF.ADD', 'filter', 'item'], $queue[0][0]);
    }

    // -----------------------------------------------------------------------
    // rawCommand()
    // -----------------------------------------------------------------------

    public function test_rawcommand_queues_the_args_verbatim_with_no_verb_prepended(): void
    {
        // Invoke through reflection, not $client->rawCommand(...): the @method
        // static tag for rawCommand is declared `...$commandAndArgs, $cb = null`
        // (a parameter after a variadic), which PHPStan reads as max 2 args even
        // though the concrete method is fully variadic. Reflection exercises the
        // real method without tripping the tag and without weakening any type.
        // (The malformed tag is a pre-existing src docblock issue — see report.)
        $client = shapingClient();
        $rawCommand = (new ReflectionClass(Client::class))->getMethod('rawCommand');
        $rawCommand->invoke($client, 'CONFIG', 'GET', 'maxmemory');

        $queue = shapingQueue($client);
        $this->assertSame(['CONFIG', 'GET', 'maxmemory'], $queue[0][0]);
        $this->assertNull($queue[0][1]);
    }

    public function test_rawcommand_pops_a_trailing_callable(): void
    {
        $client = shapingClient();
        $cb = function () {};
        $client->rawCommand('PING', $cb);

        $queue = shapingQueue($client);
        $this->assertSame(['PING'], $queue[0][0]);
        $this->assertSame($cb, $queue[0][1]);
    }

    public function test_rawcommand_throws_invalidargumentexception_spl_not_the_package_exception_when_empty(): void
    {
        $client = shapingClient();

        $this->assertThrows(\InvalidArgumentException::class, 'rawCommand requires at least the command name', function () use ($client) { return $client->rawCommand(); });
    }

    public function test_rawcommand_throws_invalidargumentexception_when_only_a_callable_is_passed(): void
    {
        // The callable is popped first, leaving zero args -> the empty check fires.
        $client = shapingClient();

        $this->assertThrows(\InvalidArgumentException::class, null, function () use ($client) { return $client->rawCommand(function () {}); });

        // And nothing was queued.
        $this->assertSame([], shapingProp($client, '_queue'));
    }

    // -----------------------------------------------------------------------
    // select() / auth() argument shaping
    // -----------------------------------------------------------------------

    public function test_select_shapes_a_select_command_with_the_db_number(): void
    {
        $client = shapingClient();
        $client->select(3);

        $queue = shapingQueue($client);
        $this->assertSame(['SELECT', 3], $queue[0][0]);
        // No dummy callback: coroutine mode must be able to suspend. The
        // formatter still records the selected DB when the reply arrives.
        $this->assertNull($queue[0][1]);
        $raw = shapingProp($client, '_queue');
        $this->assertIsCallable($raw[0][3]);
    }

    public function test_auth_shapes_an_auth_command_with_a_single_password(): void
    {
        $client = shapingClient();
        $client->auth('s3cret');

        $queue = shapingQueue($client);
        $this->assertSame(['AUTH', 's3cret'], $queue[0][0]);
    }

    public function test_auth_shapes_an_auth_command_with_a_username_password_array_acl_auth(): void
    {
        $client = shapingClient();
        $client->auth(['alice', 's3cret']);

        $queue = shapingQueue($client);
        $this->assertSame(['AUTH', 'alice', 's3cret'], $queue[0][0]);
    }

    // -----------------------------------------------------------------------
    // error() getter
    // -----------------------------------------------------------------------

    public function test_error_returns_the_empty_string_by_default_and_the_stored_error_after_one_is_set(): void
    {
        $client = shapingClient();
        $this->assertSame('', $client->error());

        $prop = (new ReflectionClass(Client::class))->getProperty('_error');
        $prop->setAccessible(true);
        $prop->setValue($client, 'Workerman Redis Wait Timeout (600 seconds)');

        $this->assertSame('Workerman Redis Wait Timeout (600 seconds)', $client->error());
    }
}
