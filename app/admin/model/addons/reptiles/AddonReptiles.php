<?php
/*
 * @Program: TaoLer 2023/3/20
 * @FilePath: app\admin\model\addons\AddonReptiles.php
 * @Description: AddonReptiles.php
 * @LastEditTime: 2023-03-20 13:53:29
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\model\addons\reptiles;

use think\Model;

class AddonReptiles extends Model
{
    public function getList($page,$limit)
    {
        $data = $this::order('create_time desc')->paginate([
            'list_rows' => $limit,
            'page' => $page
        ])->order('create_time desc')->append(['idsnum'])->toArray();

        if($data['total']) {
            return json(['code'=>0,'msg'=>'ok','count'=>$data['total'],'data'=>$data['data']]);
        } else {
            return json(['code'=>-1,'msg'=>'no data','data'=>'']);
        }

    }

    public function getIdsNumAttr($value,$data)
    {
        if(!is_null($data['ids'])) {
            return count(explode(',', $data['ids']));
        }

        return 0;
    }
}