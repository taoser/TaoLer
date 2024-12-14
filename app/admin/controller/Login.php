<?php
/**
 * @Program: TaoLer 2023/3/11
 * @FilePath: app\admin\controller\login.php
 * @Description: 管理后台登录页
 * @LastEditTime: 2023-03-11 10:16:42
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller;

use think\facade\View;
use think\facade\Request;
use think\facade\Session;
use app\admin\validate\Admin;
use think\exception\ValidateException;

class Login extends AdminBaseController
{
	// 登录
	public function index()
	{	
		if(Request::isAjax()){
			$data = Request::param();

			try{
				validate(Admin::class)
				->scene('Login')
				->check($data);	
			} catch(ValidateException $e){
				return json(['code'=>-1,'msg'=>$e->getError()]);
			}

			$user = new \app\admin\model\Admin();
			$result = $user->login($data);

			return $result;
		}
		return View::fetch('login');
	}
	
	//注册
	public function reg()
	{
		if(Session::has('admin_id')){
			return redirect('index/index');
		}
		
		return View::fetch('reg');
	}
	
	//忘记密码
	public function forget()
	{
		if(Session::has('admin_id')){
			return redirect('index/index');
		}
		
		return View::fetch('forget');
	}
	
	
}