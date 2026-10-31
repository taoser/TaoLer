<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-10-31 12:45:32
 * @LastEditors: TaoLer
 * @Description: 管理员实体类
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\entity\Admin.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\entity;


use think\facade\Session;
use think\facade\Config;
use think\exception\ValidateException;
use app\common\helper\PasswordHash;
use app\admin\validate\Admin as AdminValidate;
use app\exception\BusinessException;


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
		//将用户数据写入Session
		if(!empty($data['remember'])) {
			Session::set('admin', $adminInfo, 7 * 24 * 3600);
		} else {
			Session::set('admin', $adminInfo);
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

		if($data['password'] == $data['currentPassword']) {
			throw new BusinessException('新密码和当前密码不能相同');
		}

		$admin = $this->find($data['id']);
		if(!password_verify($data['currentPassword'], $admin['password'])) {
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
		if($admin == 1) {
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
	
}
