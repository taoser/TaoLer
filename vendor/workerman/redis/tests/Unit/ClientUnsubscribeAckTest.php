<?php

use Workerman\Redis\Client;

/*
|--------------------------------------------------------------------------
| handleUnsubscribeAck() lock-clearing bookkeeping (pure logic, no server)
|--------------------------------------------------------------------------
|
| handleUnsubscribeAck() is a protected method with no socket/event-loop
| dependency: given an UNSUBSCRIBE ack array [kind, channel, remainingCount]
| it decides whether to keep the subscribe lock (remaining > 0) or tear it
| down — clearing $_subscribe, dropping a pinned SUBSCRIBE entry from the
| queue head, and firing+clearing any registered unsubscribe completion
| callbacks.
|
| Driven entirely via reflection: build a no-constructor Client, seed the
| relevant protected props, invoke the method, assert the post-state.
*/

function ackClient(): Client
{
    return (new ReflectionClass(Client::class))->newInstanceWithoutConstructor();
}

function ackSet(Client $client, string $prop, $value): void
{
    $p = (new ReflectionClass(Client::class))->getProperty($prop);
    $p->setAccessible(true);
    $p->setValue($client, $value);
}

function ackGet(Client $client, string $prop)
{
    $p = (new ReflectionClass(Client::class))->getProperty($prop);
    $p->setAccessible(true);

    return $p->getValue($client);
}

function ackInvoke(Client $client, $result): void
{
    $m = (new ReflectionClass(Client::class))->getMethod('handleUnsubscribeAck');
    $m->setAccessible(true);
    $m->invoke($client, $result);
}

final class ClientUnsubscribeAckTest extends \Tests\TestCase
{
    public function test_keeps_the_lock_and_holds_callbacks_when_channels_still_remain_remaining_0(): void
    {
        $client = ackClient();
        ackSet($client, '_subscribe', true);
        $fired = false;
        ackSet($client, '_unsubscribeCallbacks', [function () use (&$fired) {
            $fired = true;
        }]);
        ackSet($client, '_queue', [[['SUBSCRIBE', 'a'], time(), null]]);

        // Ack: unsubscribed from one channel, 1 still remaining.
        ackInvoke($client, ['unsubscribe', 'a', 1]);

        $this->assertTrue(ackGet($client, '_subscribe'));   // lock held
        $this->assertFalse($fired);                          // callbacks NOT fired
        $this->assertCount(1, ackGet($client, '_unsubscribeCallbacks')); // still queued
        // Pinned SUBSCRIBE entry untouched.
        $this->assertCount(1, ackGet($client, '_queue'));
    }

    public function test_clears_the_lock_drops_the_pinned_subscribe_entry_and_fires_callbacks_when_remaining_is_0(): void
    {
        $client = ackClient();
        ackSet($client, '_subscribe', true);
        $calls = [];
        ackSet($client, '_unsubscribeCallbacks', [
            function ($ok, $c) use (&$calls) {
                $calls[] = $ok;
            },
            function ($ok, $c) use (&$calls) {
                $calls[] = $ok;
            },
        ]);
        ackSet($client, '_queue', [[['SUBSCRIBE', 'a'], time(), null]]);

        ackInvoke($client, ['unsubscribe', 'a', 0]);

        $this->assertFalse(ackGet($client, '_subscribe'));          // lock cleared
        $this->assertSame([], ackGet($client, '_queue'));                 // pinned entry dropped
        $this->assertSame([true, true], $calls);                          // both callbacks fired with (true, $this)
        $this->assertSame([], ackGet($client, '_unsubscribeCallbacks'));  // callbacks cleared
    }

    public function test_treats_a_missing_remaining_count_element_as_0_fail_safe_teardown(): void
    {
        // Malformed ack with no [2] element: the safe mode is to unlock, not to
        // stay locked forever.
        $client = ackClient();
        ackSet($client, '_subscribe', true);
        ackSet($client, '_unsubscribeCallbacks', []);
        ackSet($client, '_queue', [[['PSUBSCRIBE', 'p*'], time(), null]]);

        ackInvoke($client, ['unsubscribe']);   // no count element

        $this->assertFalse(ackGet($client, '_subscribe'));
        $this->assertSame([], ackGet($client, '_queue'));   // PSUBSCRIBE head also dropped
    }

    public function test_does_not_drop_a_non_stream_queue_head_when_tearing_down(): void
    {
        // If the head isn't a SUBSCRIBE/PSUBSCRIBE/SSUBSCRIBE entry it must survive.
        $client = ackClient();
        ackSet($client, '_subscribe', true);
        ackSet($client, '_unsubscribeCallbacks', []);
        ackSet($client, '_queue', [[['GET', 'somekey'], time(), null]]);

        ackInvoke($client, ['unsubscribe', 'a', 0]);

        $this->assertFalse(ackGet($client, '_subscribe'));
        // The GET entry is NOT a stream head, so it stays in the queue.
        $queue = ackGet($client, '_queue');
        $this->assertCount(1, $queue);
        $this->assertSame(['GET', 'somekey'], $queue[array_key_first($queue)][0]);
    }

    public function test_handles_an_empty_queue_gracefully_during_teardown(): void
    {
        $client = ackClient();
        ackSet($client, '_subscribe', true);
        ackSet($client, '_unsubscribeCallbacks', []);
        ackSet($client, '_queue', []);

        ackInvoke($client, ['unsubscribe', 'a', 0]);

        $this->assertFalse(ackGet($client, '_subscribe'));
        $this->assertSame([], ackGet($client, '_queue'));
    }
}
