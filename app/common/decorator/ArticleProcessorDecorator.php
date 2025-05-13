<?php

namespace app\common\decorator;

// 装饰器基类，实现 ArticleProcessor 接口
abstract class ArticleProcessorDecorator implements ArticleProcessor {
    protected $articleProcessors = [];

    public function addProcessor(ArticleProcessor $processor) {
        $this->articleProcessors[] = $processor;
        return $this;
    }

    public function process($data) {
        foreach ($this->articleProcessors as $processor) {
            $data = $processor->process($data);
        }
        return $data;
    }
}