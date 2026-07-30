<?php

namespace think\worker;

use think\worker\concerns\InteractsWithConduit;
use think\worker\concerns\InteractsWithHttp;
use think\worker\concerns\InteractsWithQueue;
use think\worker\concerns\InteractsWithServer;
use think\worker\concerns\WithApplication;
use think\worker\concerns\WithContainer;

class Manager
{
    use InteractsWithServer,
        InteractsWithHttp,
        InteractsWithQueue,
        InteractsWithConduit,
        WithApplication,
        WithContainer;

    protected function initialize(): void
    {
        $this->prepareHttp();
        $this->prepareQueue();
        $this->prepareConduit();
    }
}
