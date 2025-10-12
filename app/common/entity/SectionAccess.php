<?php

namespace app\common\entity;

class SectionAccess extends BaseEntity
{
   public function getSectionAccess($name, $num = 10)
   {
        $sid = Section::where('alias', $name)->value('id');
        return $this->where('section_id', $sid)->where('status', 1)->limit($num)->select();
   }
}