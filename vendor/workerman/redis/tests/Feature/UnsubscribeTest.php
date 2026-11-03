<?php

/*
|--------------------------------------------------------------------------
| UNSUBSCRIBE / PUNSUBSCRIBE / SUNSUBSCRIBE
|--------------------------------------------------------------------------
|
| subscribe()/pSubscribe()/sSubscribe() lock the connection: process()
| refuses to send anything while $this->_subscribe is true. The explicit
| unsubscribe* methods write the teardown frame straight to the socket
| (bypassing that lock), and the subscribe callback's new unsubscribe-ack
| case clears the lock once the server reports zero remaining subscriptions.
|
| The proof in every test below is the same: subscribe, then a Workerman
| Timer fires the unsubscribe a few hundred ms later (after the SUBSCRIBE
| ack has registered), and a *subsequent ordinary command* on the SAME
| client actually completes — which can only happen if the lock cleared.
*/

final class UnsubscribeTest extends \Tests\RedisTestCase
{
    public function test_unsubscribe_clears_the_lock_so_a_later_command_fires(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->subscribe(['pest:unsub:t1:chan'], function ($channel, $message) {});
            \Workerman\Timer::add(0.3, function () use ($redis, $emit) {
                // No channels => drop them all. The PING queued right after is
                // held back until the unsubscribe ack clears the lock.
                $redis->unsubscribe();
                $redis->ping(function ($pong) use ($emit) {
                    $emit($pong);
                });
            }, [], false);
PHP
        , 5);

        $this->assertSame('PONG', $result);
    }

    public function test_unsubscribe_fires_its_completion_callback_when_fully_unsubscribed(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->subscribe(['pest:unsub:t2:chan'], function ($channel, $message) {});
            \Workerman\Timer::add(0.3, function () use ($redis, $emit) {
                $redis->unsubscribe('pest:unsub:t2:chan', function ($ok) use ($emit) {
                    $emit($ok);
                });
            }, [], false);
PHP
        , 5);

        $this->assertTrue($result);
    }

    public function test_punsubscribe_clears_a_pattern_subscription_so_a_later_command_fires(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->pSubscribe(['pest:unsub:t3:*'], function ($pattern, $channel, $message) {});
            \Workerman\Timer::add(0.3, function () use ($redis, $emit) {
                $redis->pUnsubscribe();
                $redis->set('pest:unsub:t3:probe', 'ok', function () use ($redis, $emit) {
                    $redis->get('pest:unsub:t3:probe', function ($value) use ($emit) {
                        $emit($value);
                    });
                });
            }, [], false);
PHP
        , 5);

        $this->assertSame('ok', $result);
    }

    public function test_sunsubscribe_clears_a_shard_subscription_so_a_later_command_fires(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->sSubscribe(['pest:unsub:t4:chan'], function ($channel, $message) {});
            \Workerman\Timer::add(0.3, function () use ($redis, $emit) {
                $redis->sUnsubscribe();
                $redis->ping(function ($pong) use ($emit) {
                    $emit($pong);
                });
            }, [], false);
PHP
        , 5);

        $this->assertSame('PONG', $result);
    }

    public function test_partial_unsubscribe_keeps_the_lock_until_the_last_channel_is_dropped(): void
    {
        // Subscribed to two channels. Dropping one leaves a subscription, so the
        // connection stays locked and a queued PING must NOT fire yet. Dropping the
        // second clears the lock and the PING finally goes through. This both
        // proves the partial-vs-full teardown contract and exercises the
        // remaining > 0 branch of handleUnsubscribeAck().
        $result = runInWorker(<<<'PHP'
            $redis->subscribe(['pest:unsub:t6:a', 'pest:unsub:t6:b'], function ($channel, $message) {});
            \Workerman\Timer::add(0.3, function () use ($redis, $emit) {
                $pinged = false;
                $redis->unsubscribe('pest:unsub:t6:a');
                $redis->ping(function ($pong) use (&$pinged) {
                    $pinged = true;
                });
                \Workerman\Timer::add(0.3, function () use ($redis, $emit, &$pinged) {
                    $lockedDuringPartial = ($pinged === false);
                    $redis->unsubscribe('pest:unsub:t6:b');
                    \Workerman\Timer::add(0.3, function () use ($emit, &$pinged, $lockedDuringPartial) {
                        $emit([
                            'lockedDuringPartial' => $lockedDuringPartial,
                            'pingedAfterFull'     => $pinged,
                        ]);
                    }, [], false);
                }, [], false);
            }, [], false);
PHP
        , 5);

        $this->assertTrue($result['lockedDuringPartial']);
        $this->assertTrue($result['pingedAfterFull']);
    }

    public function test_unsubscribe_is_a_no_op_still_fires_its_callback_when_not_subscribed(): void
    {
        // Never entered subscribe mode — unsubscribe must not touch the wire
        // (that would orphan a reply), but should still honour the callback.
        $result = runInWorker(<<<'PHP'
            $redis->unsubscribe('pest:unsub:t5:chan', function ($ok) use ($emit) {
                $emit($ok);
            });
PHP
        , 5);

        $this->assertTrue($result);
    }
}
