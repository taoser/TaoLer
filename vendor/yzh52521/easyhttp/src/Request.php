<?php

namespace yzh52521\EasyHttp;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;

use GuzzleHttp\Middleware;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ConnectException;

/**
 * @method \yzh52521\EasyHttp\Response body()
 * @method \yzh52521\EasyHttp\Response array()
 * @method \yzh52521\EasyHttp\Response json()
 * @method \yzh52521\EasyHttp\Response headers()
 * @method \yzh52521\EasyHttp\Response header(string $header)
 * @method \yzh52521\EasyHttp\Response status()
 * @method \yzh52521\EasyHttp\Response successful()
 * @method \yzh52521\EasyHttp\Response ok()
 * @method \yzh52521\EasyHttp\Response redirect()
 * @method \yzh52521\EasyHttp\Response clientError()
 * @method \yzh52521\EasyHttp\Response serverError()
 */
class Request
{
    /**
     * \GuzzleHttp\Client单例
     * @var array
     */
    private static $instances = [];

    /**
     * \GuzzleHttp\Client;
     * @var Client
     */
    protected $client;

    /**
     * Body格式
     * @var string
     */
    protected $bodyFormat;

    /**
     * The raw body for the request.
     *
     * @var string
     */
    protected $pendingBody;

    protected $isRemoveBodyFormat = false;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $promises = [];

    /**
     * 并发次数
     * @var
     */
    protected $concurrency;

    /**
     *
     * @var HandlerStack
     */
    protected $handlerStack;


    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->client = $this->getInstance();

        $this->bodyFormat   = 'form_params';
        $this->options      = [
            'http_errors' => false,
        ];
        $this->handlerStack = HandlerStack::create(new CurlHandler());
    }

    /**
     *  Request destructor.
     */
    public function __destruct()
    {

    }

    /**
     * 获取单例
     * @return mixed
     */
    public function getInstance()
    {
        $name = get_called_class();

        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new Client();
        }

        return self::$instances[$name];
    }

    public function removeOptions()
    {
        $this->bodyFormat         = 'form_params';
        $this->isRemoveBodyFormat = false;
        $this->options            = [
            'http_errors' => false,
            'verify'      => false
        ];
        return $this;
    }

    public function asForm()
    {
        $this->bodyFormat = 'form_params';
        $this->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded']);

        return $this;
    }

    public function asJson()
    {
        $this->bodyFormat = 'json';
        $this->withHeaders(['Content-Type' => 'application/json']);

        return $this;
    }

    public function asMultipart(string $name, string $contents, string $filename = null, array $headers = [])
    {
        $this->bodyFormat = 'multipart';

        $this->options = array_filter([
            'name'     => $name,
            'contents' => $contents,
            'headers'  => $headers,
            'filename' => $filename,
        ]);

        return $this;
    }

    public function withMiddleware(callable $middleware)
    {
        $this->handlerStack->push($middleware);

        $this->options['handler'] = $this->handlerStack;

        return $this;
    }

    public function withRequestMiddleware(callable $middleware)
    {
        $this->handlerStack->push(Middleware::mapRequest($middleware));

        $this->options['handler'] = $this->handlerStack;

        return $this;
    }

    public function withResponseMiddleware(callable $middleware)
    {
        $this->handlerStack->push(Middleware::mapResponse($middleware));

        $this->options['handler'] = $this->handlerStack;

        return $this;
    }

    public function withHost(string $host)
    {
        $this->options['base_uri'] = $host;

        return $this;
    }

    public function withOptions(array $options)
    {
        unset($this->options[$this->bodyFormat], $this->options['body']);

        $this->options = array_merge_recursive($this->options, $options);

        return $this;
    }

    public function withCert(string $path, string $password)
    {
        $this->options['cert'] = [$path, $password];

        return $this;
    }

    public function withHeaders(array $headers)
    {
        $this->options = array_merge_recursive($this->options, [
            'headers' => $headers,
        ]);

        return $this;
    }

    public function withBody($content, $contentType = 'application/json')
    {
        $this->bodyFormat = 'body';

        $this->options['headers']['Content-Type'] = $contentType;

        $this->pendingBody = $content;

        return $this;
    }

    public function withBasicAuth(string $username, string $password)
    {
        $this->options['auth'] = [$username, $password];

        return $this;
    }

    public function withDigestAuth(string $username, string $password)
    {
        $this->options['auth'] = [$username, $password, 'digest'];

        return $this;
    }

    public function withUA(string $ua)
    {
        $this->options['headers']['User-Agent'] = trim($ua);

        return $this;
    }

    public function withToken(string $token, string $type = 'Bearer')
    {
        $this->options['headers']['Authorization'] = trim($type . ' ' . $token);

        return $this;
    }

    public function withCookies(array $cookies, string $domain)
    {
        $this->options = array_merge_recursive($this->options, [
            'cookies' => CookieJar::fromArray($cookies, $domain),
        ]);

        return $this;
    }

    public function withProxy($proxy)
    {
        $this->options['proxy'] = $proxy;

        return $this;
    }

    public function withVersion($version)
    {
        $this->options['version'] = $version;

        return $this;
    }

    public function maxRedirects(int $max)
    {
        $this->options['allow_redirects']['max'] = $max;

        return $this;
    }

    public function withRedirect($redirect = false)
    {
        $this->options['allow_redirects'] = $redirect;

        return $this;
    }

    public function withVerify($verify = false)
    {
        $this->options['verify'] = $verify;

        return $this;
    }

    public function withStream($boolean = false)
    {
        $this->options['stream'] = $boolean;

        return $this;
    }

    public function concurrency(int $times)
    {
        $this->concurrency = $times;

        return $this;
    }

    public function retry(int $retries = 1, int $sleep = 0)
    {
        $this->handlerStack->push((new Retry())->handle($retries, $sleep));

        $this->options['handler'] = $this->handlerStack;

        return $this;
    }

    public function delay(int $seconds)
    {
        $this->options['delay'] = $seconds * 1000;

        return $this;
    }

    public function timeout(float $seconds)
    {
        $this->options['timeout'] = $seconds;

        return $this;
    }

    public function connectTimeout(float $seconds)
    {
        $this->options['connect_timeout'] = $seconds;

        return $this;
    }

    /**
     * @param string|resource $to
     * @return $this
     */
    public function sink($to)
    {
        $this->options['sink'] = $to;

        return $this;
    }

    public function removeBodyFormat()
    {
        $this->isRemoveBodyFormat = true;
        return $this;
    }

    public function debug($class)
    {
        $logger = new Logger(function ($level, $message, array $context) use ($class) {
            $class::log($level, $message);
        }, function ($request, $response, $reason) {
            $requestBody = $request->getBody();
            $requestBody->rewind();

            //请求头
            $requestHeaders = [];

            foreach ((array)$request->getHeaders() as $k => $vs) {
                foreach ($vs as $v) {
                    $requestHeaders[] = "$k: $v";
                }
            }

            //响应头
            $responseHeaders = [];

            foreach ((array)$response->getHeaders() as $k => $vs) {
                foreach ($vs as $v) {
                    $responseHeaders[] = "$k: $v";
                }
            }

            $uri  = $request->getUri();
            $path = $uri->getPath();

            if ($query = $uri->getQuery()) {
                $path .= '?' . $query;
            }

            return sprintf(
                "Request %s\n%s %s HTTP/%s\r\n%s\r\n\r\n%s\r\n--------------------\r\nHTTP/%s %s %s\r\n%s\r\n\r\n%s",
                $uri,
                $request->getMethod(),
                $path,
                $request->getProtocolVersion(),
                join("\r\n", $requestHeaders),
                $requestBody->getContents(),
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                join("\r\n", $responseHeaders),
                $response->getBody()->getContents()
            );
        });
        $this->handlerStack->push($logger);
        $this->options['handler'] = $this->handlerStack;

        return $this;
    }

    public function attach(string $name, string $contents, string $filename = null, array $headers = [])
    {
        $this->options['multipart'] = array_filter([
            'name'     => $name,
            'contents' => $contents,
            'headers'  => $headers,
            'filename' => $filename,
        ]);

        return $this;
    }

    public function get(string $url, array $query = [])
    {
        $params = parse_url($url, PHP_URL_QUERY);

        parse_str($params ?: '', $result);

        $this->options['query'] = array_merge($result, $query);

        return $this->request('GET', $url, $query);
    }

    public function post(string $url, array $data = [])
    {
        $this->options[$this->bodyFormat] = $data;

        return $this->request('POST', $url, $data);
    }

    public function patch(string $url, array $data = [])
    {
        $this->options[$this->bodyFormat] = $data;

        return $this->request('PATCH', $url, $data);
    }

    public function put(string $url, array $data = [])
    {
        $this->options[$this->bodyFormat] = $data;

        return $this->request('PUT', $url, $data);
    }

    public function delete(string $url, array $data = [])
    {
        $this->options[$this->bodyFormat] = $data;

        return $this->request('DELETE', $url, $data);
    }

    public function head(string $url, array $data = [])
    {
        $this->options[$this->bodyFormat] = $data;

        return $this->request('HEAD', $url, $data);
    }

    public function options(string $url, array $data = [])
    {
        $this->options[$this->bodyFormat] = $data;

        return $this->request('OPTIONS', $url, $data);
    }

    public function getAsync(string $url, $query = null, callable $success = null, callable $fail = null)
    {
        is_callable($query) || $this->options['query'] = $query;

        return $this->requestAsync('GET', $url, $query, $success, $fail);
    }

    public function postAsync(string $url, $data = null, callable $success = null, callable $fail = null)
    {
        is_callable($data) || $this->options[$this->bodyFormat] = $data;

        return $this->requestAsync('POST', $url, $data, $success, $fail);
    }

    public function patchAsync(string $url, $data = null, callable $success = null, callable $fail = null)
    {
        is_callable($data) || $this->options[$this->bodyFormat] = $data;

        return $this->requestAsync('PATCH', $url, $data, $success, $fail);
    }

    public function putAsync(string $url, $data = null, callable $success = null, callable $fail = null)
    {
        is_callable($data) || $this->options[$this->bodyFormat] = $data;

        return $this->requestAsync('PUT', $url, $data, $success, $fail);
    }

    public function deleteAsync(string $url, $data = null, callable $success = null, callable $fail = null)
    {
        is_callable($data) || $this->options[$this->bodyFormat] = $data;

        return $this->requestAsync('DELETE', $url, $data, $success, $fail);
    }

    public function headAsync(string $url, $data = null, callable $success = null, callable $fail = null)
    {
        is_callable($data) || $this->options[$this->bodyFormat] = $data;

        return $this->requestAsync('HEAD', $url, $data, $success, $fail);
    }

    public function optionsAsync(string $url, $data = null, callable $success = null, callable $fail = null)
    {
        is_callable($data) || $this->options[$this->bodyFormat] = $data;

        return $this->requestAsync('OPTIONS', $url, $data, $success, $fail);
    }

    public function multiAsync(array $promises, callable $success = null, callable $fail = null)
    {
        $count = count($promises);

        $this->concurrency = $this->concurrency ?: $count;

        $requests = function () use ($promises) {
            foreach ($promises as $promise) {
                yield function () use ($promise) {
                    return $promise;
                };
            }
        };

        $fulfilled = function ($response, $index) use ($success) {
            if (!is_null($success)) {
                $response = $this->response($response);
                call_user_func_array($success, [$response, $index]);
            }
        };

        $rejected = function ($exception, $index) use ($fail) {
            if (!is_null($fail)) {
                $exception = $this->exception($exception);
                call_user_func_array($fail, [$exception, $index]);
            }
        };

        $pool = new Pool($this->client, $requests(), [
            'concurrency' => $this->concurrency,
            'fulfilled'   => $fulfilled,
            'rejected'    => $rejected,
        ]);

        $pool->promise();

        return $pool;
    }

    protected function request(string $method, string $url, array $options = [])
    {
        if (isset($this->options[$this->bodyFormat])) {
            $this->options[$this->bodyFormat] = $options;
        } else {
            $this->options[$this->bodyFormat] = $this->pendingBody;
        }
        if ($this->isRemoveBodyFormat) {
            unset($this->options[$this->bodyFormat]);
        }
        try {
            $response = $this->client->request($method, $url, $this->options);
            return $this->response($response);
        } catch (ConnectException $e) {
            throw new ConnectionException($e->getMessage(), 0, $e);
        }
    }

    /**
     * 原生请求
     * @param string $method
     * @param string $url
     * @param array $options
     * @return Response
     * @throws ConnectionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function client(string $method, string $url, array $options = [])
    {
        if (isset($this->options[$this->bodyFormat])) {
            $this->options[$this->bodyFormat] = $options;
        } else {
            $this->options[$this->bodyFormat] = $this->pendingBody;
        }
        if ($this->isRemoveBodyFormat) {
            unset($this->options[$this->bodyFormat]);
        }
        try {
            if (empty($options)) {
                $options = $this->options;
            }
            $response = $this->client->request($method, $url, $options);
            return $this->response($response);
        } catch (ConnectException $e) {
            throw new ConnectionException($e->getMessage(), 0, $e);
        }
    }

    /**
     * 原生异步请求
     * @param string $method
     * @param string $url
     * @param array $options
     * @return Response
     * @throws ConnectionException
     */
    public function clientAsync(string $method, string $url, array $options = [])
    {
        if (isset($this->options[$this->bodyFormat])) {
            $this->options[$this->bodyFormat] = $options;
        } else {
            $this->options[$this->bodyFormat] = $this->pendingBody;
        }
        if ($this->isRemoveBodyFormat) {
            unset($this->options[$this->bodyFormat]);
        }
        try {
            if (empty($options)) {
                $options = $this->options;
            }
            $response = $this->client->requestAsync($method, $url, $options);
            return $this->response($response);
        } catch (ConnectException $e) {
            throw new ConnectionException($e->getMessage(), 0, $e);
        }
    }


    protected function requestAsync(string $method, string $url, $options = null, callable $success = null, callable $fail = null)
    {
        if (is_callable($options)) {
            $successCallback = $options;
            $failCallback    = $success;
        } else {
            $successCallback = $success;
            $failCallback    = $fail;
        }

        if (isset($this->options[$this->bodyFormat])) {
            $this->options[$this->bodyFormat] = $options;
        } else {
            $this->options[$this->bodyFormat] = $this->pendingBody;
        }

        if ($this->isRemoveBodyFormat) {
            unset($this->options[$this->bodyFormat]);
        }

        try {
            $promise = $this->client->requestAsync($method, $url, $this->options);

            $fulfilled = function ($response) use ($successCallback) {
                if (!is_null($successCallback)) {
                    $response = $this->response($response);
                    call_user_func_array($successCallback, [$response]);
                }
            };

            $rejected = function ($exception) use ($failCallback) {
                if (!is_null($failCallback)) {
                    $exception = $this->exception($exception);
                    call_user_func_array($failCallback, [$exception]);
                }
            };

            $promise->then($fulfilled, $rejected);

            $this->promises[] = $promise;

            return $promise;
        } catch (ConnectException $e) {
            throw new ConnectionException($e->getMessage(), 0, $e);
        }
    }

    public function wait()
    {
        if (!empty($this->promises)) {
            \GuzzleHttp\Promise\Utils($this->promises)->wait();
        }
        $this->promises = [];
    }

    protected function response($response)
    {
        return new Response($response);
    }

    protected function exception($exception)
    {
        return new RequestException($exception);
    }

}
