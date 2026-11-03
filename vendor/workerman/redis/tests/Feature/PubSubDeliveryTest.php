<?php

/*
|--------------------------------------------------------------------------
| Plain pub/sub delivery + PUBLISH count + PUBSUB introspection
|--------------------------------------------------------------------------
|
| PubSubExtraTest covers the SHARDED family (sSubscribe/sPublish) and
| pubSub('CHANNELS'). This file fills the non-shard gaps:
|   - subscribe()  -> publish() -> message callback (channel + payload)
|   - pSubscribe() -> publish() -> pmessage callback (pattern + channel + payload)
|   - publish() receiver count (0 with no subscriber, >=1 with one)
|   - pubSub('NUMSUB', channel) and pubSub('NUMPAT')
|   - multi-channel subscribe delivery on each subscribed channel
|   - unsubscribe delivery semantics: after unsubscribe a later publish is
|     NOT delivered.
|
| Every streaming test is bounded two ways so it can never hang CI:
|   1. A SECOND client publishes from inside a Workerman\Timer that fires
|      AFTER the subscribe ack has had time to register; the message
|      callback calls $emit() once and stops.
|   2. A non-recurring Workerman\Timer fallback calls $fail('timeout ...')
|      well inside the runInWorker timeout, so a missed message fails fast
|      instead of hanging the harness.
*/

final class PubSubDeliveryTest extends \Tests\RedisTestCase
{
    public function test_subscribe_receives_a_message_published_by_a_second_client(): void
    {
        $result = runInWorker(<<<'PHP'
            $pub = new Workerman\Redis\Client(getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379');
            $redis->subscribe(['pest:g6:deliver:1'], function ($channel, $message, $client) use ($emit) {
                $emit(['channel' => $channel, 'message' => $message]);
            });
            // Publish only after the SUBSCRIBE ack has had time to register.
            \Workerman\Timer::add(0.3, function () use ($pub) {
                $pub->publish('pest:g6:deliver:1', 'plain-hello');
            }, [], false);
            // Hard timeout so a missed message fails fast (well under runInWorker's 8s).
            \Workerman\Timer::add(5, function () use ($fail) {
                $fail('timeout: no message delivered to subscribe()');
            }, [], false);
PHP
        , 8);

        $this->assertIsArray($result);
        $this->assertSame('pest:g6:deliver:1', $result['channel']);
        $this->assertSame('plain-hello', $result['message']);
    }

    public function test_psubscribe_receives_a_pmessage_matching_the_pattern(): void
    {
        $result = runInWorker(<<<'PHP'
            $pub = new Workerman\Redis\Client(getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379');
            $redis->pSubscribe(['pest:g6:news:*'], function ($pattern, $channel, $message, $client) use ($emit) {
                $emit(['pattern' => $pattern, 'channel' => $channel, 'message' => $message]);
            });
            \Workerman\Timer::add(0.3, function () use ($pub) {
                $pub->publish('pest:g6:news:sports', 'goal!');
            }, [], false);
            \Workerman\Timer::add(5, function () use ($fail) {
                $fail('timeout: no pmessage delivered to pSubscribe()');
            }, [], false);
PHP
        , 8);

        $this->assertIsArray($result);
        $this->assertSame('pest:g6:news:*', $result['pattern']);
        $this->assertSame('pest:g6:news:sports', $result['channel']);
        $this->assertSame('goal!', $result['message']);
    }

    public function test_publish_returns_0_when_no_client_is_subscribed(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->publish('pest:g6:count:none', 'into-the-void', function ($n) use ($emit) {
                $emit($n);
            });
PHP
        );

        $this->assertSame(0, $result);
    }

    public function test_publish_returns_at_least_1_when_a_client_is_subscribed(): void
    {
        $result = runInWorker(<<<'PHP'
            $sub = new Workerman\Redis\Client(getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379');
            $sub->subscribe(['pest:g6:count:one'], function ($channel, $message) {});
            // Publish after the subscriber has registered, then report the count.
            \Workerman\Timer::add(0.3, function () use ($redis, $emit) {
                $redis->publish('pest:g6:count:one', 'hi', function ($n) use ($emit) {
                    $emit($n);
                });
            }, [], false);
            \Workerman\Timer::add(5, function () use ($fail) {
                $fail('timeout: publish count callback never fired');
            }, [], false);
PHP
        , 8);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function test_pubsub_numsub_reports_the_subscriber_count_for_a_channel(): void
    {
        $result = runInWorker(<<<'PHP'
            $sub = new Workerman\Redis\Client(getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379');
            $sub->subscribe(['pest:g6:numsub:chan'], function ($channel, $message) {});
            \Workerman\Timer::add(0.3, function () use ($redis, $emit) {
                $redis->pubSub('NUMSUB', 'pest:g6:numsub:chan', function ($reply) use ($emit) {
                    $emit($reply);
                });
            }, [], false);
            \Workerman\Timer::add(5, function () use ($fail) {
                $fail('timeout: pubSub NUMSUB callback never fired');
            }, [], false);
PHP
        , 8);

        // NUMSUB returns a flat [channel, count, ...] array.
        $this->assertIsArray($result);
        $this->assertContains('pest:g6:numsub:chan', $result);
        $idx = array_search('pest:g6:numsub:chan', $result, true);
        $this->assertGreaterThanOrEqual(1, (int) $result[$idx + 1]);
    }

    public function test_pubsub_numpat_reports_the_number_of_active_patterns(): void
    {
        $result = runInWorker(<<<'PHP'
            $sub = new Workerman\Redis\Client(getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379');
            $sub->pSubscribe(['pest:g6:numpat:*'], function ($pattern, $channel, $message) {});
            \Workerman\Timer::add(0.3, function () use ($redis, $emit) {
                $redis->pubSub('NUMPAT', function ($n) use ($emit) {
                    $emit($n);
                });
            }, [], false);
            \Workerman\Timer::add(5, function () use ($fail) {
                $fail('timeout: pubSub NUMPAT callback never fired');
            }, [], false);
PHP
        , 8);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function test_subscribe_to_multiple_channels_delivers_on_the_one_that_is_published(): void
    {
        $result = runInWorker(<<<'PHP'
            $pub = new Workerman\Redis\Client(getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379');
            $redis->subscribe(['pest:g6:multi:a', 'pest:g6:multi:b'], function ($channel, $message, $client) use ($emit) {
                // Report the first message; we publish to channel b specifically.
                $emit(['channel' => $channel, 'message' => $message]);
            });
            \Workerman\Timer::add(0.3, function () use ($pub) {
                $pub->publish('pest:g6:multi:b', 'on-b');
            }, [], false);
            \Workerman\Timer::add(5, function () use ($fail) {
                $fail('timeout: no message delivered to multi-channel subscribe()');
            }, [], false);
PHP
        , 8);

        $this->assertIsArray($result);
        $this->assertSame('pest:g6:multi:b', $result['channel']);
        $this->assertSame('on-b', $result['message']);
    }

    public function test_after_unsubscribe_a_later_publish_is_not_delivered(): void
    {
        // Subscribe, then unsubscribe, then publish. The message callback must
        // NEVER fire (the subscription is gone). We prove the negative by waiting
        // out a bounded window after the publish and reporting "not received";
        // if the callback DID fire it emits "received" and the assertion fails.
        $result = runInWorker(<<<'PHP'
            $pub = new Workerman\Redis\Client(getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379');
            $delivered = false;
            $redis->subscribe(['pest:g6:gone:chan'], function ($channel, $message) use (&$delivered) {
                $delivered = true;
            });
            // After the subscribe ack lands, fully unsubscribe; once that ack clears
            // the lock, publish to the (now dead) channel and wait a window.
            \Workerman\Timer::add(0.3, function () use ($redis, $pub, $emit, &$delivered) {
                $redis->unsubscribe('pest:g6:gone:chan', function () use ($redis, $pub, $emit, &$delivered) {
                    $pub->publish('pest:g6:gone:chan', 'should-not-arrive');
                    // Give any (erroneous) delivery time to land, then report.
                    \Workerman\Timer::add(0.8, function () use ($emit, &$delivered) {
                        $emit($delivered ? 'received' : 'not received');
                    }, [], false);
                });
            }, [], false);
            \Workerman\Timer::add(6, function () use ($fail) {
                $fail('timeout: unsubscribe-then-publish probe never completed');
            }, [], false);
PHP
        , 9);

        $this->assertSame('not received', $result);
    }
}
