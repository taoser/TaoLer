<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-09 12:28:21
 * @LastEditTime: 2026-09-09 21:46:17
 * @LastEditors: TaoLer
 * @Description: 文章标签表
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\model\ArticleFlag.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
declare (strict_types = 1);

namespace app\model;

use think\model\concern\SoftDelete;
use app\observer\ArticleObserver;
use app\common\helper\IdEncode;
use think\facade\Route;

class ArticleFlag extends BaseModel
{

    //文章关联栏目表
    public function article()
    {
        return $this->belongsTo(Article::class);
    }


}