<?php

use GuzzleHttp\Client;
use React\EventLoop\Loop;
use Symfony\Component\Process\Process;
use function Ratchet\Client\connect;

$process = null;
beforeAll(function () use (&$process) {
    $process = new Process(['php', 'think', 'worker'], STUB_DIR, [
        'PHP_WEBSOCKET_ENABLE' => 'true',
        'PHP_QUEUE_ENABLE'     => 'false',
        'PHP_HOT_ENABLE'       => 'false',
    ]);
    $process->start();
    $wait = 0;

    while (!$process->getOutput()) {
        $wait++;
        if ($wait > 30) {
            throw new Exception('server start failed');
        }
        sleep(1);
    }
});

afterAll(function () use (&$process) {
    echo $process->getOutput();
    $process->stop();
});

beforeEach(function () {
    $this->httpClient = new Client([
        'base_uri'    => 'http://127.0.0.1:8080',
        'cookies'     => true,
        'http_errors' => false,
        'timeout'     => 1,
    ]);
});

it('http', function () {
    $response = $this->httpClient->get('/');

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($response->getBody()->getContents())
        ->toBe('hello world');
});

it('websocket', function () {
    $connected = 0;
    $messages  = [];
    connect('ws://127.0.0.1:8080/websocket')
        ->then(function (\Ratchet\Client\WebSocket $conn) use (&$connected, &$messages) {
            $connected++;
            $conn->on('message', function ($msg) use ($conn, &$messages) {
                $messages[] = (string) $msg;
                $conn->close();
            });
        });

    connect('ws://127.0.0.1:8080/websocket')
        ->then(function (\Ratchet\Client\WebSocket $conn) use (&$connected, &$messages) {
            $connected++;
            $conn->on('message', function ($msg) use ($conn, &$messages) {
                $messages[] = (string) $msg;
                $conn->close();
            });

            $conn->send('hello');
        });

    Loop::get()->run();

    expect($connected)->toBe(2);
    expect($messages)->toBe(['hello', 'hello']);
});
