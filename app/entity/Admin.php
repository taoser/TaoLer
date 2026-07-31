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
use think\Model;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Config;
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
        //查询用户
        $admin = $this->where('username', $data['username'])->whereNull('delete_time')->find();

		if(is_null($admin)){
			throw new Exception('用户名或密码错误');
		}

		if($admin['status'] != 1){
			throw new Exception('用户被禁用或未审核,请联系管理员');
		}

		$result = PasswordHash::verify($data['password'], $admin['password']);

		if($result['ok']) {
			
			//将用户数据写入Session
			Session::set('admin_id',$admin['id']);
			Session::set('admin_name', $admin['username']);
			
			if(isset($data['remember'])){
				$salt = Config::get('taoler.salt');
				//加密auth存入cookie
				$auth = md5($admin['username'].$salt).":".$admin['id'];
				Cookie::set('adminAuth', $auth,604800);
			}

			$admin->last_login_time = date('Y-m-d H:i:s');
			$admin->last_login_ip = request()->ip();
			return $admin->save();
        }

		return false;
	
    }

	public function edit(array $data)
	{
		if(empty($data['password'])){
			unset($data['password']);
		} else {
			$data['password'] = PasswordHash::make($data['password']);
		}

		return $this->update($data);
	}
	
	//修改密码
	public function setpass($data)
	{
		$admin = $this->find($data['admin_id']);

		if(!password_verify($data['oldPassword'], $admin['password'])){
			throw new Exception('当前密码错误');
		}
		
		if($data['password'] != $data['repassword']){
			throw new Exception('两次密码不一致');
		}

		$data['password'] = PasswordHash::make($data['password']);
		$admin->password = $data['password'];

		return $admin->save();
	
	}
	
}
