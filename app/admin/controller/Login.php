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

use think\Request;
use think\Response;
use think\facade\View;
use think\facade\Session;
use think\exception\ValidateException;

class Login extends AdminBaseController
{
	// 登录
	public function index(Request $request)
	{
		if (!$request->isPost()) {
			return View::fetch('login');
		}

		$data = $request->post(['account', 'password', 'captcha', 'remember']);
		$admin = new \app\entity\Admin();
		$admin->login($data);
		
		return json(['code' => 0, 'msg' => '登陆成功', 'url' => (string) url('admin-index')]);
		
	}
	
	// 注册
	public function register()
	{
		return View::fetch('register');
	}
	
	//忘记密码
	public function forget()
	{
		return View::fetch('forget');
	}
	
	
}