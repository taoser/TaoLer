<?php

namespace yzh52521\EasyHttp;

use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;

class Retry
{

    public function handle($retries,$sleep)
    {
       return  Middleware::retry($this->decider($retries), $this->delay($sleep));
    }

    protected function decider(int $times)
    {
        return function (
            $retries,
            Request $request,
            Response $response = null,
            RequestException $exception = null
        ) use ($times) {
            // 超过最大重试次数，不再重试
            if ($retries >= $times) {
                return false;
            }
            return $exception instanceof ConnectException || $exception instanceof ServerException || ($response && $response->getStatusCode() >= 500);
        };
    }

    /**
     * 返回一个匿名函数，该匿名函数返回下次重试的时间（毫秒）
     * @param int $retry_delay
     * @return \Closure
     */
    protected function delay(int $retry_delay)
    {
        return function ($retries) use ($retry_delay) {
            return $retry_delay * $retries;
        };
    }
}
