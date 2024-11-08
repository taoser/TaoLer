<?php

namespace app\admin\model\addons\taocai;

use think\Model;

class AddonTaocai extends Model
{

    public function getList($page, $limit)
    {
        $data = $this::paginate([
            'list_rows' => $limit,
            'page' => $page
        ])->toArray();

        if($data['total']) {
            return json(['code'=>0,'msg'=>'ok','data'=>$data['data']]);
        }
        return json(['code'=>-1,'msg'=>'no data']);
    }

    public function getStatusAttr($value)
    {
        $status = [0 => '等待配发', 1 => '配发成功', 2 => '正常'];
        return $status[$value];
    }
}