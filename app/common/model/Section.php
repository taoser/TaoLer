<?php

namespace app\common\model;

class Section extends BaseModel
{
    public function sectionAccess()
    {
        return $this->hasMany(sectionAccess::class);
    }
}