<?php

namespace app\common\decorator;

class ArticleDecorator
{
    private $decorators = [];

    public function addDecorator($decorator) {
        $this->decorators[] = $decorator;
    }

    public function process($data) {
        foreach ($this->decorators as $decorator) {
            $decorator->process($data);
        }
    }
}