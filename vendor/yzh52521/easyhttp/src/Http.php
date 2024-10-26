<?php

namespace yzh52521\EasyHttp;


/**
 * @method static \yzh52521\EasyHttp\Request asJson()
 * @method static \yzh52521\EasyHttp\Request asForm()
 * @method static \yzh52521\EasyHttp\Request asMultipart(string $name, string $contents, string|null $filename = null, array $headers)
 * @method static \yzh52521\EasyHttp\Request attach(string $name, string $contents, string|null $filename = null, array $headers)
 *
 * @method static \yzh52521\EasyHttp\Request withRedirect(bool|array $redirect)
 * @method static \yzh52521\EasyHttp\Request withStream(bool $boolean)
 * @method static \yzh52521\EasyHttp\Request withVerify(bool|string $verify)
 * @method static \yzh52521\EasyHttp\Request withHost(string $host)
 * @method static \yzh52521\EasyHttp\Request withHeaders(array $headers)
 * @method static \yzh52521\EasyHttp\Request withBody($content,$contentType='application/json')
 * @method static \yzh52521\EasyHttp\Request withBasicAuth(string $username, string $password)
 * @method static \yzh52521\EasyHttp\Request withDigestAuth(string $username, string $password)
 * @method static \yzh52521\EasyHttp\Request withUA(string $ua)
 * @method static \yzh52521\EasyHttp\Request withToken(string $token, string $type = 'Bearer')
 * @method static \yzh52521\EasyHttp\Request withCookies(array $cookies, string $domain)
 * @method static \yzh52521\EasyHttp\Request withProxy(string|array $proxy)
 * @method static \yzh52521\EasyHttp\Request withVersion(string $version)
 * @method static \yzh52521\EasyHttp\Request withOptions(array $options)
 * @method static \yzh52521\EasyHttp\Request withMiddleware(callable $middleware)
 * @method static \yzh52521\EasyHttp\Request withRequestMiddleware(callable $middleware)
 * @method static \yzh52521\EasyHttp\Request withResponseMiddleware(callable $middleware)
 *
 * @method static \yzh52521\EasyHttp\Request debug($class)
 * @method static \yzh52521\EasyHttp\Request retry(int $retries=1,int $sleep=0)
 * @method static \yzh52521\EasyHttp\Request delay(int $seconds)
 * @method static \yzh52521\EasyHttp\Request timeout(float $seconds)
 * @method static \yzh52521\EasyHttp\Request connectTimeout(float $seconds)
 * @method static \yzh52521\EasyHttp\Request sink(string|resource $to)
 * @method static \yzh52521\EasyHttp\Request concurrency(int $times)
 * @method static \yzh52521\EasyHttp\Request removeBodyFormat()
 * @method static \yzh52521\EasyHttp\Request maxRedirects(int $max)
 *
 * @method static \yzh52521\EasyHttp\Response get(string $url, array $query = [])
 * @method static \yzh52521\EasyHttp\Response post(string $url, array $data = [])
 * @method static \yzh52521\EasyHttp\Response patch(string $url, array $data = [])
 * @method static \yzh52521\EasyHttp\Response put(string $url, array $data = [])
 * @method static \yzh52521\EasyHttp\Response delete(string $url, array $data = [])
 * @method static \yzh52521\EasyHttp\Response head(string $url, array $data = [])
 * @method static \yzh52521\EasyHttp\Response options(string $url, array $data = [])
 * @method static \yzh52521\EasyHttp\Response client(string $method, string $url, array $options = [])
 * @method static \yzh52521\EasyHttp\Response clientAsync(string $method, string $url, array $options = [])
 *
 * @method static \GuzzleHttp\Promise\PromiseInterface getAsync(string $url, array|null $query = null, callable $success = null, callable $fail = null)
 * @method static \GuzzleHttp\Promise\PromiseInterface postAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \GuzzleHttp\Promise\PromiseInterface patchAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \GuzzleHttp\Promise\PromiseInterface putAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \GuzzleHttp\Promise\PromiseInterface deleteAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \GuzzleHttp\Promise\PromiseInterface headAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \GuzzleHttp\Promise\PromiseInterface optionsAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \GuzzleHttp\Pool multiAsync(array $promises, callable $success = null, callable $fail = null)
 * @method static void wait()
 */

class Http extends Facade
{
    protected $facade = Request::class;
}
