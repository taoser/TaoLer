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
use think\facade\Db;
use think\facade\Session;
use app\oil\model\Station;
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
    public function login(array $data)
    {
        //查询用户
        $admin = $this->where('username', $data['username'])->where('delete_time', 0)->find();

		if(is_null($admin)){
			throw new Exception('用户名或密码错误');
		}

		if($admin['status'] !=1){
			// return json(['code' => -1,'msg'=> '用户被禁用或未审核,请联系管理员']);
			throw new Exception('用户被禁用或未审核,请联系管理员');
		}

		$result = PasswordHash::verify($data['password'], $admin['password']);

		if($result['ok']){
			
			//将用户数据写入Session
			Session::set('admin_id',$admin['id']);
			Session::set('admin_name', $admin['username']);
			
			if(isset($data['remember'])){
				$salt = Config::get('taoler.salt');
				//加密auth存入cookie
				$auth = md5($admin['username'].$salt).":".$admin['id'];
				Cookie::set('adminAuth', $auth,604800);
			}

			$this->where('id', $admin['id'])->update([
				'last_login_time'	=> time(),
				'last_login_ip'		=> request()->ip(),
			]);

			return true;
        }

		return false;
	
    }
	
	//修改密码
	public function setpass($data)
	{
		$admin = $this->find($data['admin_id']);
		$salt = substr(md5($admin['create_time']),-6);
		$oldPassword = $this->pass($salt,$data['oldPassword']);
		if($oldPassword != $admin['password']){
			return json(['code'=>-1,'msg'=>'当前密码错误']);
		}
		
		if($data['password'] != $data['repassword']){
			return json(['code'=>-1,'msg'=>'两次密码不一致']);
		}

		$data['password'] = substr_replace(md5($data['password']),$salt,0,6); 
		$admin->password = $data['password'];
		$result = $admin->save();
		
		if($result){
			$res = ['code'=>0,'msg'=>'修改密码成功'];
		} else {
			$res = ['code'=>-1,'msg'=>'修改密码失败'];
		}
		
		return json($res);
	}
	
	//加密规则 加密字符串，原始秘密
	protected function pass($salt, $pass)
	{
		$pwd = substr_replace(md5($pass),$salt,0,6);
		return md5($pwd);
	}
	
}
