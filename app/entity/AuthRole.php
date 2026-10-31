<?php

namespace app\entity;

class AuthRole extends BaseEntity
{	
	/**
	 * 获取角色列表
	 *
	 * @return array
	 */
	public function getList(): array
	{
		$query = $this->order('sort asc');
		$count = $query->count();
		return [
			'count' => $count,
			'data' => $query->select()->toArray(),
		];
	}

	public function saveData(array $data): bool
	{
		if(empty($data['id'])){
			$result = $this->save($data);
		} else {
			$role = $this->find($data['id']);
			$role->name = $data['name'];
			$role->remark = $data['remark'];
			$role->sort = $data['sort'];
			$result = $role->save();
		}
		if(!$result){
			throw new BusinessException('保存失败', 400);
		}
		return $result;
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
