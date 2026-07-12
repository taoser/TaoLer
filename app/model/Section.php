<?php

namespace app\model;

class Section extends BaseModel
{
    public function sectionAccess()
    {
        return $this->hasMany(sectionAccess::class);
    }
}