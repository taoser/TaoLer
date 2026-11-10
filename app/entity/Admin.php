<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-11-06 16:32:36
 * @LastEditors: TaoLer
 * @Description: 管理员实体类
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\entity\Admin.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\entity;

use think\facade\Db;
use think\facade\Cache;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Config;
use think\exception\ValidateException;
use app\exception\BusinessException;
use app\admin\validate\Admin as AdminValidate;
use app\common\helper\JwtAuth;
use app\common\helper\PasswordHash;

class Admin extends BaseEntity
{

	/**
	 * 登陆校验
	 * @param array $data
	 * @return bool
	 */
    public function login(array $data): bool
    {
		$account = $data['account'];
		$password = $data['password'];
		$captcha = $data['captcha'];

		// 验证失败
		if(!captcha_check($captcha)){
			throw new BusinessException('验证码错误', 400);
		}
		
		if (empty($account) || empty($password)) {
			throw new BusinessException('账号和密码不能为空', 400);
        }

        // 查询用户
        $admin = $this->where(function ($query) use ($account) {
			$query->where('username', $account)
					->whereOr('mobile', $account)
					->whereOr('email', $account);
			}
		)
		->find();;

		if(is_null($admin)){
			throw new BusinessException('用户或密码错误', 400);
		}

		if($admin['status'] != 1){
			throw new BusinessException('用户被禁用或未审核,请联系管理员', 1);
		}

		['ok' => $ok, 'update' => $update, 'new_hash' => $new_hash] = PasswordHash::verify($data['password'], $admin['password']);

		if(!$ok) {
			throw new BusinessException('用户或密码错误', 400);
        }

		$adminInfo = ['id' => $admin['id'], 'username' => $admin['username']];
		Session::set('admin', $adminInfo);
		//将用户数据写入Session
		if(!empty($data['remember'])) {
			$token = JwtAuth::encode([
                'id'		=> $admin['id'],
                'username'  => $admin['username']
            ]);
			// admin模块名
        	$adminModuleName = '/' . trim(system_config('admin_module', 'admin'), '/');
			Cookie::set('admin_token', $token, ['expire' => 7 * 24 * 3600, 'path' => $adminModuleName]);
		} else {
			Cookie::delete('admin_token');
		}

		$admin->last_login_time = date('Y-m-d H:i:s');
		$admin->last_login_ip = request()->ip();

		$res = $admin->save();
		if(!$res){
			throw new BusinessException('登录失败');
		}
		return $res;
    }

	public function add(array $data): bool
	{
		$data['password'] = PasswordHash::make($data['password']);

		$res = $this->allowField(['username', 'mobile', 'email', 'password'])->save($data);
		if(!$res){
			throw new BusinessException('添加管理员失败');
		}
		return $res;
	}

	/**
	 * 编辑用户信息
	 * @param array $data
	 * @return bool
	 */
	public function edit(array $data): bool
	{
		$admin = $this->find($data['id']);
		if(is_null($admin)){
			throw new BusinessException('管理员不存在');
		}
		$admin->mobile = $data['mobile'];
		$admin->email = $data['email'];
		if(!empty($data['password'])){
			$admin->password = PasswordHash::make($data['password']);
		}
		if(!empty($data['remarks'])){
			$admin->remarks = $data['remarks'];
		}
		
		return $admin->save();
	}
	
	/**
	 * 设置新密码
	 * @param array $data
	 * @return bool
	 */
	public function setNewPassword(array $data)
	{
		if($data['password'] !== $data['confirm_password']) {
			throw new BusinessException('两次密码不一致');
		}

		if($data['password'] == $data['current_password']) {
			throw new BusinessException('新密码和当前密码不能相同');
		}

		$admin = $this->find($data['id']);
		if(!password_verify($data['current_password'], $admin['password'])) {
			throw new BusinessException('当前密码错误');
		}
		$data['password'] = PasswordHash::make($data['password']);
		$admin->password = $data['password'];

		$res = $admin->save();
		if(!$res){
			throw new BusinessException('设置新密码失败');
		}
		return $res;
	
	}

	/**
	 * 获取管理员列表
	 * @param string $account 账号
	 * @return array
	 */
	public function getList(?string $account = null): array
	{
		$count = 0;
		$data = [];
		
		$query = $this->field('id,mobile,email,avatar,username,remarks,status,last_login_ip,last_login_time,create_time');
		if(!empty($account)) {
			$query->where(function ($q) use ($account) {
				$q->where('username', $account)
				->whereOr('mobile', $account)
				->whereOr('email', $account);
			});
		}
		$count = $query->count();
		$data = $query->select()->toArray();

		return ['count' => $count, 'data' => $data];
	}

	/**
	 * 审核管理员
	 * @param array $data
	 * @return bool
	 */
	public function audit(array $data): bool
	{
		if($data['id'] == 1) {
            throw new BusinessException('超级管理员禁止操作');
        }

		$admin = $this->find($data['id']);
		if(is_null($admin)){
			throw new BusinessException('管理员不存在');
		}
		
		$admin->status = $data['status'];

		$res = $admin->save();
		if(!$res){
			throw new BusinessException('审核失败');
		}
		return $res;
	}

	/**
	 * 获取管理员权限名称
	 * 查询构造器方法
	 * @param int $adminId 管理员ID
	 * @return array
	 */
	public function getRulesById(int $adminId): array
	{
		$rules = Cache::get('rules_'.$adminId);
		if ($rules !== null) {
			return $rules;
		}

		// 超级管理员返回全部权限
		if ($adminId === 1) {
            $permissions = AuthRule::field('id,pid,name,type,title,icon,sort')
			->with(['lang' => function($query) {
					$query->field('auth_rule_id,lang,title');
				}
			])
			->where('status', 1)
			->select();
        } else {
			// 获取用户的权限
			$permissions = AuthRule::with([
				'lang' => function($query) {
						$query->field('auth_rule_id,lang,title');
				}
			])
			->distinct(true) // 去重
			->alias('ru')
			->field('ru.id,ru.pid,ru.name,ru.type,ru.title,ru.icon,ru.sort')
			->join('auth_role_rule rr', 'rr.auth_rule_id = ru.id')
			->join('auth_admin_role ro', 'ro.auth_role_id = rr.auth_role_id')
			->where('ro.admin_id', $adminId)
			->where('ru.status', 1)
			->select();
		}
		$adminModuleName = '/' . trim(system_config('admin_module', 'admin'), '/');

		$data = [
			// 路径
			'path'			=> [],
			// 按钮权限
			'permission' 	=> [],
			// 菜单
			'menu'			=> []
		];
		foreach ($permissions as  $v) {
			// 路径权限: 处理权限名称，将斜杠替换为点，并转换为小写
			$data['path'][] = str_replace('.', '/', strtolower(trim($v['name'])));
			// 按钮权限: 处理权限名称，将斜杠替换为点，并转换为小写
			if($v['type'] == 2) {
				$data['permission'][] = str_replace('/', '.', strtolower(trim($v['name'])));
			}
			// 菜单: 处理权限名称，将斜杠替换为点，并转换为小写
			$data['menu'][] = [
				'id'	=> $v['id'],
				'title' => $v['title'],
				'icon'  => $v['icon'],
				'href'  => (string) url($adminModuleName . '/' . str_replace('.', '/', $v['name'])),
				'pid'   => $v['pid'],
				'sort'  => $v['sort'],
				'type'  => $v['type']
			];
		}

		Cache::set('rules_'.$adminId, $data, 5 * 60); // 缓存5小时

		return $data;
	}

	/**
	 * 获取管理员按钮权限
	 * 模型方法
	 * @return array
	 */
	public function getPermission(): array
	{
		$rules = $this->getRulesById($this->id);
		return $rules['permission'];
	}

	/**
	 * 获取管理员菜单权限
	 * 模型方法
	 * @return array
	 */
	public function getMenu(): array
	{
		$rules = $this->getRulesById($this->id);
		return $rules['menu'];
	}

	/**
	 * 检查管理员是否有某个权限
	 * @param string $name 权限名称
	 * @return bool
	 */
	public function hasPerm(string $name): bool
	{
		// 超级管理员返回true
		if ($this->id === 1) {
			return true;
		}
	
		$rulePaths = $this->getRulesById($this->id)['path'];

        return in_array($name, $rulePaths);
	}
	
}
