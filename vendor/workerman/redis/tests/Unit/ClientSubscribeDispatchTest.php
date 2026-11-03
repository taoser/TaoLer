<?php

use Workerman\Redis\Client;

/*
|--------------------------------------------------------------------------
| Group 9 close-out — subscribe-family dispatch arms (pure logic, no server)
|--------------------------------------------------------------------------
|
| subscribe() / pSubscribe() / sSubscribe() each wrap the user callback in a
| `$new_cb` closure that switches on the streamed reply's response-type token.
| Most arms (message / pmessage / smessage and the subscribe-ack) are covered
| by the Feature pub/sub suite, but three arms are awkward to drive over a
| real socket:
|
|   1. the `if (!$result) { echo $this->error(); return; }` guard — fires when
|      onMessage hands the wrapper a falsey result (an error frame);
|   2. the unsubscribe-family ack arm, which delegates to handleUnsubscribeAck()
|      (its own bookkeeping is unit-tested in ClientUnsubscribeAckTest); and
|   3. the `default:` unknown-response-type arm, a diagnostic sink the server
|      never legitimately triggers.
|
| All three are reachable in-process: queue the subscribe, pull the stored
| wrapper out of the queue via reflection, and invoke it with a crafted
| reply. process() is inert ($_connection is null), so queueing is a pure
| append. The diagnostic arms write to stdout via echo; we wrap the invocation
| in an output buffer and assert on the captured text so the arm is both
| executed and observed without leaking into the test output.
*/

function subClient(): Client
{
    return (new ReflectionClass(Client::class))->newInstanceWithoutConstructor();
}

/**
 * Pull the wrapper callback ($new_cb) that a subscribe-family method stored as
 * the queued entry's callback (entry index [2]).
 *
 * @return callable
 */
function subWrapper(Client $client): callable
{
    $prop = (new ReflectionClass(Client::class))->getProperty('_queue');
    $prop->setAccessible(true);
    /** @var array<int, mixed> $queue */
    $queue = $prop->getValue($client);
    $entry = $queue[array_key_first($queue)];
    $cb = $entry[2];
    assert(is_callable($cb));

    return $cb;
}

function subProp(Client $client, string $name)
{
    $prop = (new ReflectionClass(Client::class))->getProperty($name);
    $prop->setAccessible(true);

    return $prop->getValue($client);
}

function subSet(Client $client, string $name, $value): void
{
    $prop = (new ReflectionClass(Client::class))->getProperty($name);
    $prop->setAccessible(true);
    $prop->setValue($client, $value);
}

/**
 * Run $fn while capturing stdout; return the captured string.
 *
 * @param callable():void $fn
 */
function captureEcho(callable $fn): string
{
    ob_start();
    $fn();
    $out = ob_get_clean();

    return $out === false ? '' : $out;
}

final class ClientSubscribeDispatchTest extends \Tests\TestCase
{
    // -----------------------------------------------------------------------
    // the `!$result` error-guard arm (echo $this->error(); return;)
    // -----------------------------------------------------------------------

    public function test_subscribe_wrapper_echoes_the_stored_error_and_bails_when_handed_a_falsey_result(): void
    {
        $client = subClient();
        $userCalled = false;
        $client->subscribe('chan', function () use (&$userCalled) {
            $userCalled = true;
        });
        subSet($client, '_error', 'boom-subscribe');
        $wrapper = subWrapper($client);

        $out = captureEcho(function () use ($wrapper) { return $wrapper(false); });

        $this->assertSame('boom-subscribe', $out);
        $this->assertFalse($userCalled);
    }

    public function test_psubscribe_wrapper_echoes_the_stored_error_and_bails_when_handed_a_falsey_result(): void
    {
        $client = subClient();
        $userCalled = false;
        $client->pSubscribe('p*', function () use (&$userCalled) {
            $userCalled = true;
        });
        subSet($client, '_error', 'boom-psub');
        $wrapper = subWrapper($client);

        $out = captureEcho(function () use ($wrapper) { return $wrapper(false); });

        $this->assertSame('boom-psub', $out);
        $this->assertFalse($userCalled);
    }

    public function test_ssubscribe_wrapper_echoes_the_stored_error_and_bails_when_handed_a_falsey_result(): void
    {
        $client = subClient();
        $userCalled = false;
        $client->sSubscribe('chan', function () use (&$userCalled) {
            $userCalled = true;
        });
        subSet($client, '_error', 'boom-ssub');
        $wrapper = subWrapper($client);

        $out = captureEcho(function () use ($wrapper) { return $wrapper(false); });

        $this->assertSame('boom-ssub', $out);
        $this->assertFalse($userCalled);
    }

    // -----------------------------------------------------------------------
    // the message / pmessage / smessage delivery arms
    // -----------------------------------------------------------------------

    public function test_subscribe_wrapper_forwards_a_message_frame_as_channel_payload_client(): void
    {
        $client = subClient();
        $seen = null;
        $client->subscribe('chan', function ($channel, $payload, $c) use (&$seen) {
            $seen = [$channel, $payload, $c];
        });
        $wrapper = subWrapper($client);

        $wrapper(['message', 'chan', 'hello']);

        $this->assertSame('chan', $seen[0]);
        $this->assertSame('hello', $seen[1]);
        $this->assertSame($client, $seen[2]);
    }

    public function test_psubscribe_wrapper_forwards_a_pmessage_frame_as_pattern_channel_payload_client(): void
    {
        $client = subClient();
        $seen = null;
        $client->pSubscribe('p*', function ($pattern, $channel, $payload, $c) use (&$seen) {
            $seen = [$pattern, $channel, $payload, $c];
        });
        $wrapper = subWrapper($client);

        $wrapper(['pmessage', 'p*', 'pchan', 'pay']);

        $this->assertSame('p*', $seen[0]);
        $this->assertSame('pchan', $seen[1]);
        $this->assertSame('pay', $seen[2]);
        $this->assertSame($client, $seen[3]);
    }

    public function test_ssubscribe_wrapper_forwards_an_smessage_frame_as_channel_payload_client(): void
    {
        $client = subClient();
        $seen = null;
        $client->sSubscribe('chan', function ($channel, $payload, $c) use (&$seen) {
            $seen = [$channel, $payload, $c];
        });
        $wrapper = subWrapper($client);

        $wrapper(['smessage', 'chan', 'shello']);

        $this->assertSame('chan', $seen[0]);
        $this->assertSame('shello', $seen[1]);
        $this->assertSame($client, $seen[2]);
    }

    // -----------------------------------------------------------------------
    // the subscribe-ack arm (swallowed) and the unsubscribe-ack delegation
    // -----------------------------------------------------------------------

    public function test_subscribe_wrapper_swallows_the_subscribe_ack_without_invoking_the_user_cb(): void
    {
        $client = subClient();
        $userCalled = false;
        $client->subscribe('chan', function () use (&$userCalled) {
            $userCalled = true;
        });
        $wrapper = subWrapper($client);

        $wrapper(['subscribe', 'chan', 1]);

        $this->assertFalse($userCalled);
        // The lock-bearing entry is still queued (ack does not tear down on subscribe).
        $this->assertCount(1, subProp($client, '_queue'));
    }

    public function test_subscribe_wrapper_delegates_an_unsubscribe_ack_remaining_0_to_handleunsubscribeack_teardown(): void
    {
        $client = subClient();
        $client->subscribe('chan', function () {});
        // Simulate the live subscribe lock that the ack must clear.
        subSet($client, '_subscribe', true);
        $wrapper = subWrapper($client);

        $wrapper(['unsubscribe', 'chan', 0]);

        // handleUnsubscribeAck cleared the lock and dropped the pinned SUBSCRIBE head.
        $this->assertFalse(subProp($client, '_subscribe'));
        $this->assertSame([], subProp($client, '_queue'));
    }

    // -----------------------------------------------------------------------
    // the default: unknown-response-type diagnostic arm
    // -----------------------------------------------------------------------

    public function test_subscribe_wrapper_writes_a_diagnostic_for_an_unknown_response_type(): void
    {
        $client = subClient();
        $client->subscribe('chan', function () {});
        $wrapper = subWrapper($client);

        $out = captureEcho(function () use ($wrapper) { return $wrapper(['bogus-type', 'x']); });

        $this->assertStringContainsString('unknow response type for subscribe', $out);
        $this->assertStringContainsString('buffer:', $out);
    }

    public function test_psubscribe_wrapper_writes_a_diagnostic_for_an_unknown_response_type(): void
    {
        $client = subClient();
        $client->pSubscribe('p*', function () {});
        $wrapper = subWrapper($client);

        $out = captureEcho(function () use ($wrapper) { return $wrapper(['bogus-type', 'x']); });

        $this->assertStringContainsString('unknow response type for psubscribe', $out);
    }

    public function test_ssubscribe_wrapper_writes_a_diagnostic_for_an_unknown_response_type(): void
    {
        $client = subClient();
        $client->sSubscribe('chan', function () {});
        $wrapper = subWrapper($client);

        $out = captureEcho(function () use ($wrapper) { return $wrapper(['bogus-type', 'x']); });

        $this->assertStringContainsString('unknow response type for ssubscribe', $out);
    }

    // -----------------------------------------------------------------------
    // assertNoActiveStream — the second-stream guard throws (pure, no socket)
    // -----------------------------------------------------------------------

    public function test_a_second_subscribe_family_call_throws_because_a_stream_is_already_pending(): void
    {
        $client = subClient();
        $client->subscribe('chan', function () {});

        // Every subscribe-family entry point routes through assertNoActiveStream();
        // with a SUBSCRIBE already queued, streamActiveOrPending() is true.
        $this->assertThrows(\Workerman\Redis\Exception::class, 'active or pending', function () use ($client) { $client->subscribe('other', function () {}); });
        $this->assertThrows(\Workerman\Redis\Exception::class, 'one stream per', function () use ($client) { $client->pSubscribe('p*', function () {}); });
        $this->assertThrows(\Workerman\Redis\Exception::class, null, function () use ($client) { $client->sSubscribe('s', function () {}); });
    }
}
