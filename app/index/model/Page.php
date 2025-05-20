<?php

namespace app\index\model;

use app\common\model\BaseModel;

class Page extends BaseModel
{
    //文章关联栏目表
    public function cate()
    {
        return $this->belongsTo(Category::class);
    }
}