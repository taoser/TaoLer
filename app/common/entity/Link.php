<?php

namespace app\common\entity;

class Link extends BaseEntity
{
   public function getLink($num = 10)
   {
        $data = [];
        $list = $this->field('id,title,logo,url,sort,status,start_time,end_time')
        ->where('status', 1)
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
                'logo' => $v->logo,
                'url'   => $v->url,
                'sort'  => $v->sort,
            ];
        }

        return $data;
   }
}