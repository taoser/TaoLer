<?php

use Workerman\Redis\Client;
use Workerman\Redis\Exception;

final class SubscriptionTestConnection
{
    public $commands = [];
    public $onConnect;
    public $onError;
    public $onClose;
    public $onMessage;

    public function send(array $command): void
    {
        $this->commands[] = $command;
    }

    public function close(): void
    {
    }
}

final class ClientSubscribeDispatchTest extends \Tests\TestCase
{
    private function attachConnection(Client $client): SubscriptionTestConnection
    {
        $connection = new SubscriptionTestConnection();
        $class = new ReflectionClass($client);
        foreach (['_connection' => $connection, '_waiting' => false] as $name => $value) {
            $property = $class->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($client, $value);
        }
        return $connection;
    }

    private function reply(Client $client, array $reply): void
    {
        $method = new ReflectionMethod($client, 'handleSubscriptionReply');
        $method->setAccessible(true);
        $method->invoke($client, ['*', $reply]);
    }

    private function client(): Client
    {
        return (new ReflectionClass(Client::class))->newInstanceWithoutConstructor();
    }

    public function test_reconnect_restores_only_confirmed_subscriptions_and_retries_pending_changes(): void
    {
        $client = $this->client();
        $this->attachConnection($client);
        $received = [];
        $client->subscribe(['kept', 'removed'], function ($channel) use (&$received) {
            $received[] = $channel;
        });
        $this->reply($client, ['subscribe', 'kept', 1]);
        $this->reply($client, ['subscribe', 'removed', 2]);
        $client->unsubscribe('removed');
        $this->reply($client, ['unsubscribe', 'removed', 1]);
        $client->pSubscribe('pending:*', function ($pattern) use (&$received) {
            $received[] = $pattern;
        });

        $client->closeConnection();
        $connection = $this->attachConnection($client);
        $client->process();
        $this->reply($client, ['subscribe', 'kept', 1]);
        $this->reply($client, ['psubscribe', 'pending:*', 2]);
        $this->reply($client, ['message', 'kept', 'payload']);
        $this->reply($client, ['message', 'removed', 'ignored']);
        $this->reply($client, ['pmessage', 'pending:*', 'pending:one', 'payload']);

        $this->assertSame([['SUBSCRIBE', 'kept'], ['PSUBSCRIBE', 'pending:*']], $connection->commands);
        $this->assertSame(['kept', 'pending:*'], $received);
    }

    public function test_messages_use_the_latest_acknowledged_callback(): void
    {
        $client = $this->client();
        $connection = $this->attachConnection($client);
        $received = [];
        $client->subscribe('channel', function ($channel, $body) use (&$received) {
            $received[] = ['first', $body];
        });
        $client->unsubscribe('channel');
        $client->subscribe('channel', function ($channel, $body) use (&$received) {
            $received[] = ['second', $body];
        });

        $this->reply($client, ['subscribe', 'channel', 1]);
        $this->reply($client, ['message', 'channel', 'before']);
        $this->reply($client, ['unsubscribe', 'channel', 0]);
        $this->reply($client, ['subscribe', 'channel', 1]);
        $client->subscribe('channel', function ($channel, $body) use (&$received) {
            $received[] = ['third', $body];
        });
        $this->reply($client, ['message', 'channel', 'in flight']);
        $this->reply($client, ['subscribe', 'channel', 1]);
        $this->reply($client, ['message', 'channel', 'after']);

        $this->assertSame([
            ['SUBSCRIBE', 'channel'],
            ['UNSUBSCRIBE', 'channel'],
            ['SUBSCRIBE', 'channel'],
            ['SUBSCRIBE', 'channel'],
        ], $connection->commands);
        $this->assertSame([['first', 'before'], ['second', 'in flight'], ['third', 'after']], $received);
    }

    public function test_ordinary_commands_wait_for_all_unsubscribe_acknowledgements(): void
    {
        $client = $this->client();
        $connection = $this->attachConnection($client);
        $client->subscribe(['a', 'b'], function () {});
        $client->sSubscribe('shard', function () {});
        $this->reply($client, ['subscribe', 'a', 1]);
        $this->reply($client, ['subscribe', 'b', 2]);
        $this->reply($client, ['ssubscribe', 'shard', 1]);
        $client->ping(function () {});
        $completed = [];
        $client->unsubscribe(function ($ok) use (&$completed) {
            $completed[] = ['regular', $ok];
        });
        $client->sUnsubscribe('shard', 'missing', function ($ok) use (&$completed) {
            $completed[] = ['sharded', $ok];
        });

        $this->reply($client, ['unsubscribe', 'a', 1]);
        $this->reply($client, ['unsubscribe', 'b', 0]);
        $this->assertSame([], $completed);
        $this->reply($client, ['sunsubscribe', 'shard', 0]);
        $this->assertSame([], $completed);
        $this->reply($client, ['sunsubscribe', 'missing', 0]);
        $this->assertSame([['regular', true], ['sharded', true]], $completed);
        $this->assertSame([
            ['SUBSCRIBE', 'a', 'b'],
            ['SSUBSCRIBE', 'shard'],
            ['UNSUBSCRIBE', 'a', 'b'],
            ['SUNSUBSCRIBE', 'shard', 'missing'],
            ['PING'],
        ], $connection->commands);
    }

    public function test_rejected_subscribe_releases_the_connection(): void
    {
        $client = $this->client();
        $connection = $this->attachConnection($client);
        $client->subscribe([], function () {});
        $client->ping(function () {});

        $this->expectOutputString('ERR wrong number of arguments');
        $handler = new ReflectionMethod($client, 'handleSubscriptionReply');
        $handler->setAccessible(true);
        $handler->invoke($client, ['-', 'ERR wrong number of arguments']);

        $this->assertSame([['SUBSCRIBE'], ['PING']], $connection->commands);
    }

    public function test_a_subscription_cannot_be_queued_behind_monitor(): void
    {
        $client = $this->client();
        $client->monitor(function () {});

        $this->expectException(Exception::class);
        $client->subscribe('channel', function () {});
    }
}
