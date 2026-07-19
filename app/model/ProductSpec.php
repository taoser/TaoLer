<?php
namespace app\model;

class ProductSpec extends BaseModel
{
    public function child()
    {
        return $this->hasMany(ProductSpecValue::class);
    }


}
