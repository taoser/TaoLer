<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2022-04-20 10:45:41
 * @LastEditTime: 2026-09-22 12:06:16
 * @LastEditors: TaoLer
 * @Description: 文章标签关联中间表
 * @FilePath: \TaoLer\app\model\ArticleTag.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */

namespace app\model;

use think\model\Pivot;

class ArticleTag extends Pivot
{
    protected $autoWriteTimestamp = true;
}