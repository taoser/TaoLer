<?php

namespace think\worker\conduit\driver\socket;

class Event
{
    public function __construct(public $name, public $data)
    {
    }

    public static function create($name, $data)
    {
        return new self($name, $data);
    }
}
