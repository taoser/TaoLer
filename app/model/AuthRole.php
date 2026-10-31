<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-10-31 14:57:54
 * @LastEditTime: 2026-10-31 16:26:08
 * @LastEditors: TaoLer
 * @Description: 角色模型
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\model\AuthRole.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\model;


class AuthRole extends BaseModel
{
    protected function getOptions(): array 
    {
        return [
            'autoWriteTimestamp'    => true,
        ];
    }

    //角色多对多关联角色分配表
    public function GroupAccess()
    {
        return $this->hasMany('AuthGroupAccess','group_id','id');
    }
	
	//登陆校验
    public function authRuleTree()
    {
		$authRules = $this->order('sort asc')->select();
		return $this->sort($authRules);
	}
	
	public function sort($data,$pid=0)
	{
		static $arr = array();
		foreach($data as $k=> $v){
			if($v['pid']==$pid){
				$arr[] = $v;
				$this->sort($data,$v['id']);	
			}
		}
		return $arr;
	}
}
