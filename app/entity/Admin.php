<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\model\Admin.php
 * @Description: Admin
 * @LastEditTime: 2023-03-14 16:50:41
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\entity;

use Exception;
use think\exception\ValidateException;
use think\facade\Session;
use think\facade\Config;
use app\common\helper\PasswordHash;
use app\admin\validate\Admin as AdminValidate;


class Admin extends BaseEntity
{
	/**
	 * 登陆校验
	 * @param array $data
	 * @return bool
	 */
    public function login(array $data): bool
    {
		try {
			validate(AdminValidate::class)
			->scene('Login')
			->check($data);
		} catch (ValidateException $e) {
			throw new Exception($e->getError(), 1);
		}

        //查询用户
        $admin = $this->where('username', $data['username'])->find();

		if(is_null($admin)){
			throw new Exception('用户名或密码错误', -1);
		}

		if($admin['status'] != 1){
			throw new Exception('用户被禁用或未审核,请联系管理员', 1);
		}

		['ok'=>$ok,'update'=>$update,'new_hash'=>$new_hash] = PasswordHash::verify($data['password'], $admin['password']);

		if(!$ok) {
			throw new Exception('密码错误', -1);
        }

		//将用户数据写入Session
		Session::set('admin_id',$admin['id']);
		Session::set('admin_name', $admin['username']);
		
		if(isset($data['remember'])) {
			Config::set(['expire' => 604800], 'session');
			Session::set('admininfo', ['id' => $admin['id'], 'username' => $admin['username']]);
		}

		$admin->last_login_time = date('Y-m-d H:i:s');
		$admin->last_login_ip = request()->ip();

		return $admin->save();
    }

	public function edit(array $data)
	{
		if(!empty($data['password'])){
			$data['password'] = PasswordHash::make($data['password']);
		} else {
			unset($data['password']);
		}

		return $this->update($data);
	}
	
	//修改密码
	public function setpass(array $data)
	{
		if($data['password'] !== $data['repassword']){
			throw new Exception('两次密码不一致');
		}

		$admin = $this->find($data['admin_id']);

		if(!password_verify($data['oldPassword'], $admin['password'])){
			throw new Exception('当前密码错误');
		}

		$data['password'] = PasswordHash::make($data['password']);
		$admin->password = $data['password'];

		return $admin->save();
	
	}
	
}
