<?php
declare (strict_types = 1);

namespace app\event;


class ArticlePush
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}
