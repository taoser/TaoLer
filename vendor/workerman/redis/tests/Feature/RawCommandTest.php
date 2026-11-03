<?php

final class RawCommandTest extends \Tests\RedisTestCase
{
    public function test_rawcommand_sends_an_arbitrary_command_and_returns_the_reply(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:rawcmd:k1';
            $redis->del($key);
            $redis->rawCommand('SET', $key, 'rc-value', function ($setReply) use ($redis, $emit, $key) {
                $redis->rawCommand('GET', $key, function ($getReply) use ($emit, $setReply) {
                    $emit([
                        'set' => $setReply,
                        'get' => $getReply,
                    ]);
                });
            });
PHP
        );

        $this->assertIsArray($result);
        // SET returns +OK which the client normalizes to true.
        $this->assertSame(true, $result['set']);
        $this->assertSame('rc-value', $result['get']);
    }

    public function test_rawcommand_handles_a_command_with_no_args(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->rawCommand('PING', function ($reply) use ($emit) {
                $emit($reply);
            });
PHP
        );

        // PING replies with +PONG (a simple string, NOT the +OK->true normalization
        // path), so the client surfaces the literal 'PONG' string.
        $this->assertSame('PONG', $result);
    }

    public function test_rawcommand_supports_binary_safe_values(): void
    {
        $result = runInWorker(<<<'PHP'
            $key = 'pest:rawcmd:bin';
            $value = "line1\r\nline2\r\n\x00trailing";
            $redis->del($key);
            $redis->rawCommand('SET', $key, $value, function () use ($redis, $emit, $key, $value) {
                $redis->rawCommand('GET', $key, function ($got) use ($emit, $value) {
                    $emit([
                        'match'      => $got === $value,
                        'length'     => is_string($got) ? strlen($got) : -1,
                        'expect_len' => strlen($value),
                    ]);
                });
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame(true, $result['match']);
        $this->assertSame($result['expect_len'], $result['length']);
    }

    public function test_rawcommand_surfaces_server_errors(): void
    {
        $result = runInWorker(<<<'PHP'
            // Send a command Redis/Dragonfly will reject with an -ERR reply.
            // The client signals reply errors by handing the callback `false`.
            $redis->rawCommand('THIS_IS_NOT_A_COMMAND', 'arg1', function ($reply, $client) use ($emit) {
                $emit([
                    'reply'     => $reply,
                    'reply_typ' => gettype($reply),
                    'has_error' => $client->error() !== '',
                ]);
            });
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame(false, $result['reply']);
        $this->assertSame(true, $result['has_error']);
    }

    public function test_rawcommand_without_args_throws_invalidargumentexception(): void
    {
        $result = runInWorker(<<<'PHP'
            // Passing only a callable means there are no command parts left after
            // the trailing-callback pop. The guard must throw before anything is
            // queued to the wire.
            try {
                $redis->rawCommand(function ($reply) {});
                $emit(['threw' => false]);
            } catch (\InvalidArgumentException $e) {
                $emit([
                    'threw'   => true,
                    'message' => $e->getMessage(),
                ]);
            }
PHP
        );

        $this->assertIsArray($result);
        $this->assertSame(true, $result['threw']);
        $this->assertStringContainsString('rawCommand', $result['message']);
    }
}
