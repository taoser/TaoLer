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
 * @method static \yzh52521\EasyHttp\Request withBasicAuth(string $username, string $password)
 * @method static \yzh52521\EasyHttp\Request withDigestAuth(string $username, string $password)
 * @method static \yzh52521\EasyHttp\Request withUA(string $ua)
 * @method static \yzh52521\EasyHttp\Request withToken(string $token, string $type = 'Bearer')
 * @method static \yzh52521\EasyHttp\Request withCookies(array $cookies, string $domain)
 * @method static \yzh52521\EasyHttp\Request withProxy(string|array $proxy)
 * @method static \yzh52521\EasyHttp\Request withVersion(string $version)
 * @method static \yzh52521\EasyHttp\Request withOptions(array $options)
 *
 * @method static \yzh52521\EasyHttp\Request debug($class)
 * @method static \yzh52521\EasyHttp\Request retry(int $retries=1,int $sleep=0)
 * @method static \yzh52521\EasyHttp\Request delay(int $seconds)
 * @method static \yzh52521\EasyHttp\Request timeout(int $seconds)
 * @method static \yzh52521\EasyHttp\Request concurrency(int $times)
 * @method static \yzh52521\EasyHttp\Request client(string $method, string $url, array $options = [])
 * @method static \yzh52521\EasyHttp\Request clientAsync(string $method, string $url, array $options = [])
 * @method static \yzh52521\EasyHttp\Request removeBodyFormat()
 *
 * @method static \yzh52521\EasyHttp\Request get(string $url, array $query = [])
 * @method static \yzh52521\EasyHttp\Request post(string $url, array $data = [])
 * @method static \yzh52521\EasyHttp\Request patch(string $url, array $data = [])
 * @method static \yzh52521\EasyHttp\Request put(string $url, array $data = [])
 * @method static \yzh52521\EasyHttp\Request delete(string $url, array $data = [])
 * @method static \yzh52521\EasyHttp\Request head(string $url, array $data = [])
 * @method static \yzh52521\EasyHttp\Request options(string $url, array $data = [])
 *
 * @method static \yzh52521\EasyHttp\Request getAsync(string $url, array|null $query = null, callable $success = null, callable $fail = null)
 * @method static \yzh52521\EasyHttp\Request postAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \yzh52521\EasyHttp\Request patchAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \yzh52521\EasyHttp\Request putAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \yzh52521\EasyHttp\Request deleteAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \yzh52521\EasyHttp\Request headAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \yzh52521\EasyHttp\Request optionsAsync(string $url, array|null $data = null, callable $success = null, callable $fail = null)
 * @method static \yzh52521\EasyHttp\Request multiAsync(array $promises, callable $success = null, callable $fail = null)
 * @method static \yzh52521\EasyHttp\Request wait()
 */

class Http extends Facade
{
    protected $facade = Request::class;
}
