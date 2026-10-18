<?php

namespace app\entity;

use think\facade\Cache;

class SliderGroup extends BaseEntity
{
    public const CACHE_KEY = 'slider';
    /**
     * 获取所有轮播图组
     * @return array
     */
    public function getAll(): array
    {
        $cache = Cache::get(self::CACHE_KEY);
        if($cache) {
            return $cache;
        }
        $sliders = $this->with(['items' => function($query) {
            $query->field('id,group_id,title,image,url,description,start_time,end_time,option')->where('status', 1)->order('sort', 'asc');
        }])
        ->where('status', 1)
        ->order('sort', 'asc')
        ->select();

        $data = [];
        foreach($sliders as $slider) {
            if(!$slider->items->isEmpty()) {
                // 时间比较
                $now = date('Y-m-d H:i:s');
                $items = $slider->items->toArray();
                $items = array_filter($items, function($item) use($now) {
                    return $item['start_time'] <= $now && $item['end_time'] >= $now;
                });
                if(empty($items)){
                    continue;
                }
                $data[$slider->name] = $items;
            }
        }
        Cache::set(self::CACHE_KEY, $data, 3600 * 5);
        return $data;
    }

    /**
     * 获取指定轮播图组
     * @param string $name 轮播图组名称
     * @return array
     */
    public function getGroup(string $name): array
    {
        $all = $this->getAll();
        return $all[$name] ?? [];
    }
}
