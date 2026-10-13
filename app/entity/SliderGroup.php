<?php

namespace app\entity;

class SliderGroup extends BaseEntity
{
    /**
     * 获取所有轮播图组
     * @return array
     */
    public function getAll(): array
    {
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
