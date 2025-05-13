<?php

namespace app\common\entity;

class AdSlide extends BaseEntity
{
   public function getSlide($type = 1, $num = 5)
   {
        $data = [];
        $list = $this->field('id,title,type,image,url,sort,status,start_time,end_time')
        ->where([
            'status' => 1,
            'type'  => $type,
        ])
        ->order('sort', 'asc')
        ->limit($num)
        ->select();

        foreach($list as $v) {
            // 未开始
            if(!is_null($v['start_time']) && time() < strtotime($v['start_time'])) {
                continue;
            }
            // 已结束
            if(!is_null($v['end_time']) && strtotime($v['end_time']) < time()) {
                continue;
            }

            $data[] = [
                'id' => $v->id,
                'title'  => $v->title,
                'type'  => $v->type,
                'image' => $v->image,
                'url'   => $v->url,
                'sort'  => $v->sort,
            ];
        }

        return $data;
   }
}