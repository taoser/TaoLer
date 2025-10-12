<?php

namespace app\common\model;

class SectionAccess extends BaseModel
{
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}