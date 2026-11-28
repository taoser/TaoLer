<?php

final class DynamicPubSubTest extends \Tests\RedisTestCase
{
    public function test_adds_a_channel_to_an_active_subscription_and_dispatches_both_callbacks(): void
    {
        $result = runInWorker(<<<'PHP'
            $publisher = new Workerman\Redis\Client(getenv('REDIS_URL'));
            $received = [];
            $redis->subscribe('pest:streamguard:a', function ($channel, $message) use (&$received) {
                $received['first'] = [$channel, $message];
            });
            \Workerman\Timer::add(0.2, function () use ($redis, $publisher, $emit, &$received) {
                $redis->subscribe('pest:streamguard:b', function ($channel, $message) use (&$received) {
                    $received['second'] = [$channel, $message];
                });
                \Workerman\Timer::add(0.2, function () use ($publisher) {
                    $publisher->publish('pest:streamguard:a', 'one');
                    $publisher->publish('pest:streamguard:b', 'two');
                }, [], false);
                \Workerman\Timer::add(0.5, function () use ($emit, &$received) {
                    ksort($received);
                    $emit($received);
                }, [], false);
            }, [], false);
PHP
        );

        $this->assertSame([
            'first' => ['pest:streamguard:a', 'one'],
            'second' => ['pest:streamguard:b', 'two'],
        ], $result);
    }

    public function test_mixes_channels_and_patterns_queued_before_connecting(): void
    {
        $result = runInWorker(<<<'PHP'
            $publisher = new Workerman\Redis\Client(getenv('REDIS_URL'));
            $received = [];
            $channel = 'pest:streamguard:news';
            $redis->subscribe($channel, function ($channel, $message) use (&$received) {
                $received['channel'] = [$channel, $message];
            });
            $redis->pSubscribe('pest:streamguard:*', function ($pattern, $channel, $message) use (&$received) {
                $received['pattern'] = [$pattern, $channel, $message];
            });
            $redis->pSubscribe('pest:*:news', function ($pattern, $channel, $message) use (&$received) {
                $received['otherPattern'] = [$pattern, $channel, $message];
            });
            \Workerman\Timer::add(0.3, function () use ($publisher, $channel) {
                $publisher->publish($channel, 'hello');
            }, [], false);
            \Workerman\Timer::add(0.6, function () use ($emit, &$received) {
                ksort($received);
                $emit($received);
            }, [], false);
PHP
        );

        $this->assertSame([
            'channel' => ['pest:streamguard:news', 'hello'],
            'otherPattern' => ['pest:*:news', 'pest:streamguard:news', 'hello'],
            'pattern' => ['pest:streamguard:*', 'pest:streamguard:news', 'hello'],
        ], $result);
    }

    public function test_reconnect_restores_only_the_subscriptions_that_remain(): void
    {
        $result = runInWorker(<<<'PHP'
            $publisher = new Workerman\Redis\Client(getenv('REDIS_URL'));
            $received = [];
            $redis->subscribe('pest:reconnect:channel', function ($channel) use (&$received) {
                $received[] = $channel;
            });
            $redis->pSubscribe('pest:reconnect:*', function ($pattern, $channel, $message) use (&$received) {
                $received[] = [$pattern, $channel, $message];
            });
            $redis->unsubscribe();
            \Workerman\Timer::add(0.3, function () use ($redis) {
                $redis->closeConnection();
                $redis->connect();
            }, [], false);
            \Workerman\Timer::add(0.6, function () use ($publisher) {
                $publisher->publish('pest:reconnect:channel', 'after reconnect');
            }, [], false);
            \Workerman\Timer::add(0.9, function () use ($redis, $emit, &$received) {
                $redis->pUnsubscribe();
                $redis->ping(function ($pong) use ($emit, &$received) {
                    $emit([$received, $pong]);
                });
            }, [], false);
PHP
        );

        $this->assertSame([[['pest:reconnect:*', 'pest:reconnect:channel', 'after reconnect']], 'PONG'], $result);
    }

    public function test_queued_unsubscribe_waits_for_subscribe_and_frees_the_connection(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->subscribe('pest:streamguard:queued', function () {});
            $redis->unsubscribe();
            $redis->ping(function ($pong) use ($emit) {
                $emit($pong);
            });
PHP
        );

        $this->assertSame('PONG', $result);
    }
}
