<?php

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

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
	 * 权限树
	 */
    public function authRuleTree()
    {
		$authRules = $this->order('sort asc')->select();
		//return $this->sort($authRules);
		return $authRules;
	}
	/**
	 * id，pid,菜单排序
	 * @var $data 数据
	 * @var $pid 父级id
	 */
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
