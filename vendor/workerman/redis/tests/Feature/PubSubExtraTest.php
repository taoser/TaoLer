<?php

/*
|--------------------------------------------------------------------------
| Sharded pub/sub + PUBSUB introspection coverage
|--------------------------------------------------------------------------
|
| Covers SPUBLISH and SSUBSCRIBE (sharded pub/sub) plus the PUBSUB CHANNELS
| subcommand. Sharded pub/sub is a Redis 7.0+ cluster feature; on a non-
| cluster server (including Dragonfly's standalone mode, which is the CI
| target here) the commands are still implemented but behave like single-
| shard variants of PUBLISH/SUBSCRIBE — which is exactly what we need to
| confirm the wiring is correct.
|
| If a future test target lacks SPUBLISH/SSUBSCRIBE entirely, the server
| will reply with an -ERR and the assertion path will fail noisily — at
| that point the test should be gated on a server-capability probe rather
| than silently skipped.
*/

final class PubSubExtraTest extends \Tests\RedisTestCase
{
    public function test_spublish_returns_the_subscriber_count_when_nobody_is_listening(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->sPublish('pest:ps:t1:channel', 'hello', function ($n) use ($emit) {
                $emit($n);
            });
PHP
        );

        $this->assertSame(0, $result);
    }

    public function test_ssubscribe_receives_a_message_published_via_spublish(): void
    {
        $result = runInWorker(<<<'PHP'
            $sub = new Workerman\Redis\Client(getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379');
            $pub = new Workerman\Redis\Client(getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379');
            $sub->sSubscribe(['pest:ps:t2:chan'], function ($channel, $message, $client) use ($emit) {
                $emit(['channel' => $channel, 'message' => $message]);
            });
            // Give SSUBSCRIBE a moment to register before publishing.
            \Workerman\Timer::add(0.2, function () use ($pub) {
                $pub->sPublish('pest:ps:t2:chan', 'sharded-hello');
            }, [], false);
PHP
        , 5);

        $this->assertIsArray($result);
        $this->assertSame('pest:ps:t2:chan', $result['channel']);
        $this->assertSame('sharded-hello', $result['message']);
    }

    public function test_pubsub_channels_returns_active_channels_matching_a_pattern(): void
    {
        $result = runInWorker(<<<'PHP'
            $sub = new Workerman\Redis\Client(getenv('REDIS_URL') ?: 'redis://127.0.0.1:6379');
            $sub->subscribe(['pest:ps:t3:chan-a'], function ($ch, $msg) {});
            \Workerman\Timer::add(0.2, function () use ($redis, $emit) {
                $redis->pubSub('CHANNELS', 'pest:ps:t3:*', function ($channels) use ($emit) {
                    $emit($channels);
                });
            }, [], false);
PHP
        , 5);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }
}
