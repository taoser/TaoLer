<?php

namespace think\worker\websocket;

class Frame
{
    public function __construct(public int $fd, public string $data)
    {
    }
}
