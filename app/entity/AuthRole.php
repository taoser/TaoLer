<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-10-31 17:13:27
 * @LastEditTime: 2026-11-01 14:40:25
 * @LastEditors: TaoLer
 * @Description: 角色实体
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\entity\AuthRole.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\entity;

use think\facade\Db;
use app\exception\BusinessException;

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

	/**
	 * 保存角色数据
	 *
	 * @param array $data
	 * @return bool
	 */
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

	/**
	 * 获取角色权限
	 *
	 * @param int $role_id
	 * @return array
	 */
	public function getRoleRules(?int $role_id = null): array
	{
		// $roleRule = new AuthRoleRule();
		$ruleIds = Db::name('auth_role_rule')->where('auth_role_id', $role_id)->column('auth_rule_id');

		$authRule = new AuthRule();

		$authRules = $authRule->with(['lang' => function($query) {
                $query->field('auth_rule_id,lang,title');
            }
        ])
        ->field('id,pid,title,sort,level')
		->where(['status'=> 1])
		->order('sort','asc')
		->append(['title'])
		->select()
		->toArray();

		if(empty($authRules)){
			return [];
		}

		foreach($authRules as $k=> $v){
			if(in_array($v['id'], $ruleIds)){
				// 是否选中该节点
				$authRules[$k]['checked'] = true;
				// 是否展开该节点
				$authRules[$k]['spread'] = true;
			} else {
				$authRules[$k]['checked'] = false;
				$authRules[$k]['spread'] = true;
			}
		}

		return build_tree($authRules);
	}

	/**
	 * 保存角色权限
	 *
	 * @param array $data
	 * @return bool
	 */
	public function saveRoleRule($data): bool
	{
		try {
			$role = $this->find($data['role_id']);
			// 取消所有权限
			if(!isset($data['rule_ids'])){
				// throw new BusinessException('未选中任何权限，请选择权限，否则将取消所有权限', 400);

				$rules = $role->rules()->column('auth_rule_id');
				$role->rules()->detach($rules);
				return true;
			}
			// 字符串数值转换为整数数组
			$ruleIds = array_map('intval', $data['rule_ids']);
			// 同步权限 （自动增删）
			$role->rules()->sync($ruleIds);
			return true;

		} catch (\Exception $e) {
			throw new BusinessException($e->getMessage(), 400);
		}

	}

	/**
	 * 保存角色管理员
	 * 角色绑定管理员
	 * @param array $data
	 * @return bool
	 */
	public function saveAdminRole($data): bool	
	{
		try {
			$role = $this->find($data['role_id']);
			if(!isset($data['admin_ids'])){
				// throw new BusinessException('未选中任何管理员，请选择管理员，否则将取消所有管理员', 400);
				$admins = $role->admins()->column('admin_id');
				$role->admins()->detach($admins);
				return true;
			}
			// 字符串数值转换为整数数组
			$adminIds = array_map('intval', $data['admin_ids']);
			// 同步管理员 （自动增删）
			$role->admins()->sync($adminIds);
			return true;
		} catch (\Exception $e) {
			throw new BusinessException($e->getMessage(), 400);
		}
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
