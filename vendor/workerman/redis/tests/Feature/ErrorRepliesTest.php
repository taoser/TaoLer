<?php

/*
|--------------------------------------------------------------------------
| Group 7B: error-reply handling (systematic)
|--------------------------------------------------------------------------
|
| Confirms the decode error path in Client::onMessage end-to-end: a "-ERR ..."
| / "-WRONGTYPE ..." reply (RESP type '-') is delivered to the callback as
| $reply === false, and the wire message is exposed via $client->error()
| (non-empty). The connection is NOT torn down for a '-' error (only a '!'
| protocol error triggers reconnect), so the queue keeps draining.
|
| Error WORDING differs subtly across engines, so assertions check the
| DELIVERY contract (reply === false + error() non-empty + a tolerant keyword
| match) rather than pinning exact text. The four wordings used here were
| verified identical on Dragonfly (6379) and Redis (63790) via redis-cli, so
| no engine skips are required.
|
| Unique prefix per case: pest:g7:err:<n>: (shared db0).
*/

final class ErrorRepliesTest extends \Tests\RedisTestCase
{
    public function test_wrongtype_error_is_delivered_as_false_with_a_typed_error_message(): void
    {
        $result = runInWorker(<<<'PHP'
            // A list op against a string key triggers WRONGTYPE.
            $redis->set('pest:g7:err:1', 'i-am-a-string', function () use ($redis, $emit) {
                $redis->lPush('pest:g7:err:1', 'x', function ($reply, $client) use ($emit) {
                    $emit([
                        'reply'   => $reply,
                        'reply_t' => gettype($reply),
                        'error'   => $client->error(),
                    ]);
                });
            });
PHP
        );

        $this->assertSame(false, $result['reply']);
        $this->assertNotSame('', $result['error']);
        // Tolerant: error mentions WRONGTYPE / "wrong kind of value".
        $this->assertStringContainsString('wrongtype', strtolower($result['error']));
    }

    public function test_unknown_command_error_is_delivered_as_false_mentioning_the_command_is_unknown(): void
    {
        $result = runInWorker(<<<'PHP'
            $redis->rawCommand('NOTACOMMAND', 'x', function ($reply, $client) use ($emit) {
                $emit([
                    'reply' => $reply,
                    'error' => $client->error(),
                ]);
            });
PHP
        );

        $this->assertSame(false, $result['reply']);
        $this->assertNotSame('', $result['error']);
        $this->assertStringContainsString('unknown command', strtolower($result['error']));
    }

    public function test_wrong_number_of_arguments_error_is_delivered_as_false(): void
    {
        $result = runInWorker(<<<'PHP'
            // SET with only a key (no value) is a wrong-arg-count error.
            $redis->rawCommand('SET', 'pest:g7:err:3', function ($reply, $client) use ($emit) {
                $emit([
                    'reply' => $reply,
                    'error' => $client->error(),
                ]);
            });
PHP
        );

        $this->assertSame(false, $result['reply']);
        $this->assertNotSame('', $result['error']);
        // Tolerant: error mentions the argument count problem.
        $this->assertStringContainsString('wrong number of arguments', strtolower($result['error']));
    }

    public function test_value_not_an_integer_error_is_delivered_as_false(): void
    {
        $result = runInWorker(<<<'PHP'
            // INCR on a non-numeric string is a "not an integer" error.
            $redis->set('pest:g7:err:4', 'not-a-number', function () use ($redis, $emit) {
                $redis->incr('pest:g7:err:4', function ($reply, $client) use ($emit) {
                    $emit([
                        'reply' => $reply,
                        'error' => $client->error(),
                    ]);
                });
            });
PHP
        );

        $this->assertSame(false, $result['reply']);
        $this->assertNotSame('', $result['error']);
        $this->assertStringContainsString('not an integer', strtolower($result['error']));
    }

    public function test_syntax_error_from_a_bad_option_is_delivered_as_false(): void
    {
        $result = runInWorker(<<<'PHP'
            // SET with a bogus trailing option triggers a syntax error.
            $redis->rawCommand('SET', 'pest:g7:err:5', 'v', 'BOGUSOPTION', function ($reply, $client) use ($emit) {
                $emit([
                    'reply' => $reply,
                    'error' => $client->error(),
                ]);
            });
PHP
        );

        $this->assertSame(false, $result['reply']);
        $this->assertNotSame('', $result['error']);
        $this->assertStringContainsString('syntax', strtolower($result['error']));
    }

    public function test_error_is_cleared_after_a_subsequent_successful_command(): void
    {
        $result = runInWorker(<<<'PHP'
            // First force an error, then run a clean command on the SAME connection
            // (a '-' error does not reconnect) and confirm error() is reset to ''.
            $redis->incr('pest:g7:err:6:str', function () use ($redis, $emit) {
                $redis->set('pest:g7:err:6:str', 'nope', function () use ($redis, $emit) {
                    $redis->incr('pest:g7:err:6:str', function ($bad, $client) use ($redis, $emit) {
                        $errAfterFail = $client->error();
                        $redis->ping(function ($pong, $client2) use ($emit, $errAfterFail, $bad) {
                            $emit([
                                'bad'           => $bad,
                                'errAfterFail'  => $errAfterFail,
                                'pong'          => $pong,
                                'errAfterOk'    => $client2->error(),
                            ]);
                        });
                    });
                });
            });
PHP
        );

        $this->assertSame(false, $result['bad']);
        $this->assertNotSame('', $result['errAfterFail']);
        // PING replies +PONG (Workerman returns the raw 'PONG' string, not true).
        $this->assertSame('PONG', $result['pong']);
        $this->assertSame('', $result['errAfterOk']);
    }
}
