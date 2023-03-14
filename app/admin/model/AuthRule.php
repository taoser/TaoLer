<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\model\AuthRule.php
 * @Description: AuthRule
 * @LastEditTime: 2023-03-14 16:51:30
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Lang;

class AuthRule extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
	
	public function searchIdAttr($query, $value, $data)
    {
        $query->where('id', $value );      
    }

    /**
     * 获取权限列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAuthRuleTree()
    {
        $authRules = $this->field('id,pid,title,name,icon,status,ismenu,sort,create_time')->select()->toArray();
        //数组排序
        $cmf_arr = array_column($authRules, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $authRules);

        if(count($authRules)) {
            return json(['code'=>0,'msg'=>'ok','data'=>$authRules]);
        } else {
            return json(['code'=>0,'msg'=>'no data','data'=>'']);
        }
    }

    public function saveRule($data)
    {
        $res = $this->save($data);
        if($res){
            return json(['code'=>0,'msg'=>'权限成功']);
        }else{
            return json(['code'=>-1,'msg'=>'权限失败']);
        }
    }

    /**
     * 获取权限菜单数组
     *
     * @return void
     */
    public function getAuthRuleArray()
    {
        $authRules = $this->field('id,pid,title,name,icon,status,ismenu,sort,create_time')->select()->toArray();
        $ruls = [];
        foreach($authRules as $v) {
            $ruls[] = [
                'powerId'   => $v['id'],
                'powerName' => Lang::get($v['title']),
                'powerType' => $v['ismenu'],
                'powerCode' => '',
                "powerUrl"  => $v['name'],
                "openType"  => null,
                "parentId"  => $v['pid'],
                "icon"      => $v['icon'],
                "sort"      => $v['sort'],
                "enable"    => $v['status'],
                "checkArr"  => "0"

            ];
        }
        //数组排序
        $cmf_arr = array_column($ruls, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $ruls);

        if(count($ruls)) {
            return json(['code' => 0, 'msg' => 'ok', 'count' => count($ruls), 'data'=>$ruls]);
        } else {
            return json(['code' => 0, 'msg' => 'no data','count' => null,'data'=>'']);
        }
    }

}
