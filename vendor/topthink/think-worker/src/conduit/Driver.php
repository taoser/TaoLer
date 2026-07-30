<?php

namespace think\worker\conduit;

abstract class Driver
{
    abstract public function prepare();

    abstract public function connect();

    abstract public function get(string $name);

    abstract public function set(string $name, $value);

    abstract public function inc(string $name, int $step = 1);

    abstract public function sAdd(string $name, ...$value);

    abstract public function sRem(string $name, $value);

    abstract public function sMembers(string $name);

    abstract public function publish(string $name, $value);

    abstract public function subscribe(string $name, $callback);
}
