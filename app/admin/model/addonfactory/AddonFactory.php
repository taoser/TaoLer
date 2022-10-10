<?php

namespace app\admin\model\addonfactory;

use think\Model;

class AddonFactory extends Model
{
    public function getList($page,$limit)
    {
        $data = $this::paginate([
            'list_rows' => $limit,
            'page' => $page
        ])->toArray();

        if($data['total']) {
            return json(['code'=>0,'msg'=>'ok','data'=>$data['data']]);
        } else {
            return json(['code'=>-1,'msg'=>'no data','data'=>'']);
        }

    }

}