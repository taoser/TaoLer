<?php

namespace app\common\decorator;

// 装饰器基类，实现 ArticleProcessor 接口
abstract class ArticleProcessorDecorator implements ArticleProcessor
{
    /**
     * 文章处理器数组
     * @var ArticleProcessor[]
     */
    protected $articleProcessors = [];

    /**
     * 添加文章处理器
     * @param ArticleProcessor $processor 文章处理器
     * @return $this
     */
    public function addProcessor(ArticleProcessor $processor) {
        $this->articleProcessors[] = $processor;
        return $this;
    }

    /**
     * 处理文章数据
     * @param $data 文章数据
     * @return array 处理后的文章数据
     */
    public function process($data) {
        foreach ($this->articleProcessors as $processor) {
            $data = $processor->process($data);
        }
        return $data;
    }
}