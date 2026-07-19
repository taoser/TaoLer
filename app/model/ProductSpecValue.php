<?php
namespace app\model;

class ProductSpecValue extends BaseModel
{
    //文章关联栏目表
    public function spec()
    {
        return $this->belongsTo(ProductSpec::class);
    }
}