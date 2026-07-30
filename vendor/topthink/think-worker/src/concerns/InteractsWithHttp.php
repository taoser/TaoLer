<?php

namespace think\worker\concerns;

use think\App;
use think\Cookie;
use think\Event;
use think\exception\Handle;
use think\helper\Str;
use think\Http;
use think\response\View;
use think\worker\App as WorkerApp;
use think\worker\Http as WorkerHttp;
use think\worker\response\File as FileResponse;
use think\worker\response\Iterator as IteratorResponse;
use think\worker\websocket\Frame;
use think\worker\Worker;
use Throwable;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Chunk;
use Workerman\Protocols\Http\Request as WorkerRequest;
use Workerman\Protocols\Http\Response;
use function substr;

/**
 * Trait InteractsWithHttp
 * @property App $app
 * @property App $container
 */
trait InteractsWithHttp
{
    use ModifyProperty, InteractsWithWebsocket;

    protected $wsEnable = false;

    protected function prepareHttp()
    {
        if ($this->getConfig('http.enable', true)) {

            $this->wsEnable = $this->getConfig('websocket.enable', false);

            if ($this->wsEnable) {
                $this->prepareWebsocket();
            }

            $workerNum = $this->getConfig('http.worker_num', 4);
            $this->addWorker([$this, 'createHttpServer'], 'http server', $workerNum);
        }
    }

    public function createHttpServer()
    {
        $this->preloadHttp();

        $host    = $this->getConfig('http.host');
        $port    = $this->getConfig('http.port');
        $options = $this->getConfig('http.options', []);

        $server = new Worker("\\think\\worker\\protocols\\FlexHttp://{$host}:{$port}", $options);

        $server->reusePort = true;

        $server->onMessage = function (TcpConnection $connection, $data) {
            if ($data instanceof WorkerRequest) {
                if ($this->wsEnable && $this->isWebsocketRequest($data)) {
                    $this->onHandShake($connection, $data);
                } else {
                    $this->onRequest($connection, $data);
                }
            } elseif ($data instanceof Frame) {
                $this->onMessage($connection, $data);
            }
        };

        $server->onClose = function (TcpConnection $connection) {
            if ($this->wsEnable) {
                $this->onClose($connection);
            }
        };

        $server->listen();
    }

    protected function preloadHttp()
    {
        $http = $this->app->http;
        $this->app->invokeMethod([$http, 'loadMiddleware'], [], true);

        if ($this->app->config->get('app.with_route', true)) {
            $this->app->invokeMethod([$http, 'loadRoutes'], [], true);
            $route = clone $this->app->route;
            unset($this->app->route);

            $this->app->resolving(WorkerHttp::class, function ($http, App $app) use ($route) {
                $newRoute = clone $route;
                $this->modifyProperty($newRoute, $app);
                $app->instance('route', $newRoute);
            });
        }

        $middleware = clone $this->app->middleware;
        unset($this->app->middleware);

        $this->app->resolving(WorkerHttp::class, function ($http, App $app) use ($middleware) {
            $newMiddleware = clone $middleware;
            $this->modifyProperty($newMiddleware, $app);
            $app->instance('middleware', $newMiddleware);
        });

        unset($this->app->http);
        $this->app->bind(Http::class, WorkerHttp::class);
    }

    public function onRequest(TcpConnection $connection, WorkerRequest $wkRequest)
    {
        $this->runInSandbox(function (Http $http, Event $event, WorkerApp $app) use ($connection, $wkRequest) {

            $app->setInConsole(false);

            $request = $this->prepareRequest($wkRequest);

            try {
                $response = $this->handleRequest($http, $request);
                $this->prepareResponse($response);
            } catch (Throwable $e) {
                $handle = $app->make(Handle::class);
                $handle->report($e);
                $response = $handle->render($request, $e);
            }

            $this->sendResponse($connection, $request, $response, $app->cookie);

            //关闭连接
            $connection->close();

            $http->end($response);
        });
    }

    protected function handleRequest(Http $http, $request)
    {
        $level = ob_get_level();
        ob_start();

        $response = $http->run($request);

        if (ob_get_length() > 0) {
            $content = $response->getContent();
            $response->content(ob_get_contents() . $content);
        }

        while (ob_get_level() > $level) {
            ob_end_clean();
        }

        return $response;
    }

    protected function prepareRequest(WorkerRequest $wkRequest)
    {
        $header = $wkRequest->header();
        $server = [];

        foreach ($header as $key => $value) {
            $server['http_' . str_replace('-', '_', $key)] = $value;
        }

        // 重新实例化请求对象 处理请求数据
        /** @var \think\Request $request */
        $request = $this->app->make('request', [], true);;

        $queryString = $wkRequest->queryString();

        return $request
            ->setMethod($wkRequest->method())
            ->withHeader($header)
            ->withServer($server)
            ->withGet($wkRequest->get())
            ->withPost($wkRequest->post())
            ->withCookie($wkRequest->cookie())
            ->withFiles($wkRequest->file())
            ->withInput($wkRequest->rawBody())
            ->setBaseUrl($wkRequest->uri())
            ->setUrl($wkRequest->uri() . (!empty($queryString) ? '?' . $queryString : ''))
            ->setPathinfo(ltrim($wkRequest->path(), '/'));
    }

    protected function prepareResponse(\think\Response $response)
    {
        switch (true) {
            case $response instanceof View:
                $response->getContent();
                break;
        }
    }

    protected function sendResponse(TcpConnection $connection, \think\Request $request, \think\Response $response, Cookie $cookie)
    {
        switch (true) {
            case $response instanceof IteratorResponse:
                $this->sendIterator($connection, $response, $cookie);
                break;
            case $response instanceof FileResponse:
                $this->sendFile($connection, $request, $response, $cookie);
                break;
            default:
                $this->sendContent($connection, $response, $cookie);
        }
    }

    protected function sendIterator(TcpConnection $connection, IteratorResponse $response, Cookie $cookie)
    {
        $wkResponse = $this->createResponse($response, $cookie);
        $connection->send($wkResponse);

        foreach ($response as $content) {
            $connection->send($content, true);
        }
    }

    protected function sendFile(TcpConnection $connection, \think\Request $request, FileResponse $response, Cookie $cookie)
    {
        $ifNoneMatch = $request->header('If-None-Match');
        $ifRange     = $request->header('If-Range');

        $code         = $response->getCode();
        $file         = $response->getFile();
        $eTag         = $response->getHeader('ETag');
        $lastModified = $response->getHeader('Last-Modified');

        $fileSize = $file->getSize();
        $offset   = 0;
        $length   = -1;

        if ($ifNoneMatch == $eTag) {
            $code = 304;
        } elseif (!$ifRange || $ifRange === $eTag || $ifRange === $lastModified) {
            $range = $request->header('Range', '');
            if (Str::startsWith($range, 'bytes=')) {
                [$start, $end] = explode('-', substr($range, 6), 2) + [0];

                $end = ('' === $end) ? $fileSize - 1 : (int) $end;

                if ('' === $start) {
                    $start = $fileSize - $end;
                    $end   = $fileSize - 1;
                } else {
                    $start = (int) $start;
                }

                if ($start <= $end) {
                    $end = min($end, $fileSize - 1);
                    if ($start < 0 || $start > $end) {
                        $code = 416;
                        $response->header([
                            'Content-Range' => sprintf('bytes */%s', $fileSize),
                        ]);
                    } elseif ($end - $start < $fileSize - 1) {
                        $length = $end < $fileSize ? $end - $start + 1 : -1;
                        $offset = $start;
                        $code   = 206;
                        $response->header([
                            'Content-Range'  => sprintf('bytes %s-%s/%s', $start, $end, $fileSize),
                            'Content-Length' => $end - $start + 1,
                        ]);
                    }
                }
            }
        }

        $wkResponse = $this->createResponse($response, $cookie);

        if ($code >= 200 && $code < 300 && $length !== 0) {
            $wkResponse->withFile($file->getPathname(), $offset, $length);
        }

        $connection->send($wkResponse);
    }

    protected function sendContent(TcpConnection $connection, \think\Response $response, Cookie $cookie)
    {
        $response->header(['Transfer-Encoding' => 'chunked']);

        $wkResponse = $this->createResponse($response, $cookie);

        $connection->send($wkResponse);

        $content = $response->getContent();
        if ($content) {
            $contentSize = strlen($content);
            $chunkSize   = 8192;

            if ($contentSize > $chunkSize) {
                $sendSize = 0;
                do {
                    if (!$connection->send(new Chunk(substr($content, $sendSize, $chunkSize)))) {
                        break;
                    }
                } while (($sendSize += $chunkSize) < $contentSize);
            } else {
                $connection->send(new Chunk($content));
            }
        }
        $connection->send(new Chunk(''));
    }

    protected function createResponse(\think\Response $response, Cookie $cookie, $body = '')
    {
        $code   = $response->getCode();
        $header = $response->getHeader();

        $wkResponse = new Response($code, $header, $body);

        foreach ($cookie->getCookie() as $name => $val) {
            [$value, $expire, $option] = $val;
            $wkResponse->cookie($name, $value, $expire, $option['path'], $option['domain'], (bool) $option['secure'], (bool) $option['httponly'], $option['samesite']);
        }

        return $wkResponse;
    }
}
