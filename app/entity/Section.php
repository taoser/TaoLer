<?php

namespace app\entity;

class Section extends BaseEntity
{
   public function getSection($alias)
   {
        return $this->field('id,title,subtitle')->where('status', 1)->where('alias', $alias)->find();
   }
}