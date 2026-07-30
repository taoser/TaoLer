<?php

namespace think\worker\conduit\driver\socket;

class Command
{
    public $id;
    public $name;
    public $key;
    public $data;

    public static function create($name, $key, $data = null)
    {
        $packet = new self();

        $packet->name = $name;
        $packet->key  = $key;
        $packet->data = $data;

        return $packet;
    }
}
