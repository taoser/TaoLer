<?php

namespace think\worker\watcher;

interface Driver
{
    public function watch(callable $callback);
}
