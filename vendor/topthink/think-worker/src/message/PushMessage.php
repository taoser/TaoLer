<?php

namespace think\worker\message;

class PushMessage
{
    public $to;
    public $data;

    public function __construct($to, $data)
    {
        $this->to   = $to;
        $this->data = $data;
    }
}
