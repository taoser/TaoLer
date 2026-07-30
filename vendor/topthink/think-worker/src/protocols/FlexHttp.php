<?php

namespace think\worker\protocols;

use think\worker\websocket\Frame;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http;
use Workerman\Protocols\Websocket;

class FlexHttp
{
    public static function input(string $buffer, TcpConnection $connection)
    {
        if (empty($connection->context->websocketHandshake)) {
            return Http::input($buffer, $connection);
        } else {
            return Websocket::input($buffer, $connection);
        }
    }

    public static function decode(string $buffer, TcpConnection $connection)
    {
        if (empty($connection->context->websocketHandshake)) {
            return Http::decode($buffer, $connection);
        } else {
            $data = Websocket::decode($buffer, $connection);
            return new Frame($connection->id, $data);
        }
    }

    public static function encode(mixed $response, TcpConnection $connection): string
    {
        if (empty($connection->context->websocketHandshake)) {
            return Http::encode($response, $connection);
        } else {
            return Websocket::encode($response, $connection);
        }
    }
}
