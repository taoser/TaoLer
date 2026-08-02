<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\model\AuthRule.php
 * @Description: AuthRule
 * @LastEditTime: 2023-03-14 16:51:30
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Lang;
use think\Response;

class AuthRule extends BaseModel
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
        $authRules = $this->field('id,pid,title,name,icon,status,ismenu,sort,create_time')
        ->order('sort','asc')
        ->select()
        ->toArray();

        if(count($authRules)) {
            return json(['code'=>0,'msg'=>'ok','data'=>$authRules]);
        }
        return json(['code'=>0,'msg'=>'no data','data'=>'']);
    }

    public function saveRule($data)
    {
        $res = $this->save($data);
        if($res){
            return true;
        }
        return false;
    }

    /**
     * 获取权限菜单数组
     *
     * @return  Response
     */
    public function getAuthRuleArray() :Response
    {
        $authRules = $this->field('id,pid,title,name,icon,status,ismenu,sort,create_time')
        ->order('sort','asc')
        ->select()
        ->toArray();
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

        if(count($ruls)) {
            return json(['code' => 0, 'msg' => 'ok', 'count' => count($ruls), 'data'=>$ruls]);
        }

        return json(['code' => 0, 'msg' => 'no data','count' => null,'data'=>'']);
    }

}
