<?php

/*
|--------------------------------------------------------------------------
| Group 9 close-out — connection-failure reporting
|--------------------------------------------------------------------------
|
| Covers the connection-FAILED path of Client::connect() (the connect-timeout
| callback, Client.php ~649-656): constructing a Client aimed at a dead port
| WITH a connection callback must invoke that callback with (false, $client)
| and leave a non-empty error(), rather than hanging or silently swallowing
| the failure.
|
| Runs identically on Dragonfly and Redis (this is purely client-side
| connection logic).
|
| NOTE on the onConnect SELECT/AUTH prepend (Client.php 538-547): that branch
| only runs on a genuine RE-connect, where Workerman itself drives a fresh
| onConnect after the server dropped the socket. A test-driven
| closeConnection()+connect() in the same tick does NOT exercise it cleanly —
| the prepended SELECT (null-callback) desyncs reply routing for the very next
| queued command in that artificial sequence, so it cannot be asserted
| deterministically without server-side socket fault injection. It is
| documented as impractical in docs/TEST_COVERAGE_PLAN.md (Group 9 close-out)
| rather than pinned with a flaky test.
*/

final class ReconnectPrependTest extends \Tests\RedisTestCase
{
    public function test_reports_a_connection_failure_to_a_dead_port_through_the_connection_callback(): void
    {
        // Construct a Client aimed at a closed port WITH a connection callback.
        // The connection-failure path must fire that callback with (false, $client)
        // and set a non-empty error(). We use a short connect_timeout so the
        // failure is observed well within the worker timeout. 127.0.0.1:1 is
        // reserved/closed -> the connect cannot complete.
        $result = runInWorker(<<<'PHP'
            $failed = new \Workerman\Redis\Client(
                'redis://127.0.0.1:1',
                ['connect_timeout' => 2],
                function ($ok, $client) use ($emit) {
                    // First connect attempt: $ok must be false on a refused/dead port.
                    $emit([
                        'ok'    => $ok,
                        'error' => $client->error(),
                    ]);
                    // Stop the client from auto-reconnecting after we observed it.
                    $client->close();
                }
            );
PHP
        );

        $this->assertIsArray($result);
        $this->assertFalse($result['ok']);
        $this->assertIsString($result['error']);
        $this->assertNotSame('', $result['error']);
        // The failure message identifies it as a connection failure/timeout.
        $msg = strtolower($result['error']);
        $isConnFailure = str_contains($msg, 'connection') || str_contains($msg, 'timeout');
        $this->assertTrue($isConnFailure);
    }
}
