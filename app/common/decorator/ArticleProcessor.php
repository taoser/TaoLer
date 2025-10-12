<?php

namespace app\common\decorator;

// 定义一个基础的文章处理接口
interface ArticleProcessor {
    public function process($data);
}