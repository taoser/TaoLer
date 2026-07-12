<?php

namespace app\model;

class SectionAccess extends BaseModel
{
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}