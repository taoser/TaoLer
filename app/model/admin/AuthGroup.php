<?php

namespace app\admin\model;

use think\Model;
//use think\model\concern\SoftDelete;

class AuthGroup extends Model
{
    //软删除
    //use SoftDelete;
   // protected $deleteTime = 'delete_time';
    //protected $defaultSoftDelete = 0;

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
