<?php

namespace think\worker\conduit\driver\socket;

class Result
{
    public function __construct(public $id = null, public $data = null)
    {

    }

    public static function create($id = null)
    {
        return new self($id);
    }
}
