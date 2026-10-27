<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-10-19 20:38:53
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\entity\Section.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

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
           $query->field('id,section_id,name,icon,image,url,description,option')->where('status', 1)->order('sort');
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