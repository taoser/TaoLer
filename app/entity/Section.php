<?php

namespace app\entity;

use think\facade\Cache;

class Section extends BaseEntity
{
   
   public const CACHE_KEY = 'section';

   /**
    * 获取模块列表
    * @return array
    */
   public function getSections(): array
   {
       $cacheData = Cache::get(self::CACHE_KEY);
       if (!empty($cacheData)) {
           return $cacheData;
       }

       $sections = $this->with(['sectionAccess' => function($query){
           $query->field('id,section_id,name,icon,image,url,description,option')->where('status', 1);
       }])
       ->field('id,alias,title,subtitle')
       ->where('status', 1)
       ->select()
       ->toArray();

       $data = [];
       if(count($sections)){
           foreach($sections as $section){
               $data[$section['alias']] = $section;
           }
       }
       Cache::set(self::CACHE_KEY, $data);
       return $data;
   }

   /**
    * 获取模块详情
    * @param string $alias 模块别名
    * @return array
    */
   public function getSection($alias)
   {
       $sections = $this->getSections();
       return $sections[$alias] ?? [];
   }


}