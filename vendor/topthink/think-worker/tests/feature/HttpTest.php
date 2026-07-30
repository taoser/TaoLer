<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\Process\Process;

$process = null;
beforeAll(function () use (&$process) {
    $process = new Process(['php', 'think', 'worker'], STUB_DIR, [
        'PHP_WEBSOCKET_ENABLE' => 'false',
        'PHP_QUEUE_ENABLE'     => 'false',
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

it('callback route', function () {
    $response = $this->httpClient->get('/');

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($response->getBody()->getContents())
        ->toBe('hello world');
});

it('controller route', function () {
    $jar = new CookieJar();

    $response = $this->httpClient->get('/test', ['cookies' => $jar]);

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($response->getBody()->getContents())
        ->toBe('test')
        ->and($jar->getCookieByName('name')->getValue())
        ->toBe('think');
});

it('json post', function () {

    $data     = [
        'name' => 'think',
    ];
    $response = $this->httpClient->post('/json', [
        'json' => $data,
    ]);

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($response->getBody()->getContents())
        ->toBe(json_encode($data));
});

it('put and delete request', function () {
    $response = $this->httpClient->put('/');

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($response->getBody()->getContents())
        ->toBe('put');

    $response = $this->httpClient->delete('/');

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($response->getBody()->getContents())
        ->toBe('delete');
});

it('file response', function () {
    $response = $this->httpClient->get('/static/asset.txt');

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($response->getBody()->getContents())
        ->toBe(file_get_contents(STUB_DIR . '/public/asset.txt'));
});

it('sse', function () {
    $response = $this->httpClient->get('/sse', [
        'stream'  => true,
        'timeout' => 3,
    ]);

    $body = $response->getBody();

    $buffer = '';
    while (!$body->eof()) {
        $text = $body->read(1);
        if ($text == "\r") {
            continue;
        }
        $buffer .= $text;
        if ($text == "\n") {
            if ($buffer != "\n") {
                expect($buffer)->toStartWith('data: ');
            }
            $buffer = '';
        }
    }
});

it('hot update', function () {
    $response = $this->httpClient->get('/hot');

    expect($response->getStatusCode())
        ->toBe(404);

    $route = <<<PHP
<?php

use think\\facade\\Route;

Route::get('/hot', function () {
    return 'hot';
});
PHP;

    file_put_contents(STUB_DIR . '/route/hot.php', $route);

    sleep(2);

    $response = $this->httpClient->get('/hot');

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($response->getBody()->getContents())
        ->toBe('hot');
})->after(function () {
    @unlink(STUB_DIR . '/route/hot.php');
})->skipOnWindows();
