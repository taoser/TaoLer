<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-07-30 07:19:57
 * @LastEditTime: 2026-09-19 20:27:28
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\common\decorator\ArticleProcessor.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\common\decorator;

// 定义一个基础的文章处理接口
interface ArticleProcessor {
    public function process($data);
}