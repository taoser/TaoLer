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
use app\admin\validate\Admin;
use think\exception\ValidateException;

class Login extends AdminBaseController
{
	// 登录
	public function index(Request $request)
	{
		if (!$request->isPost()) {
			return View::fetch('login');
		}

		$data = $request->post(['username', 'password', 'captcha', 'remember']);
		
		try {
			validate(Admin::class)
			->scene('Login')
			->check($data);

			$admin = new \app\entity\Admin();
			$result = $admin->login($data);
			if ($result) {
				return json(['code' => 0, 'msg' => '登陆成功', 'url' => (string) url('admin-index')]);
			}

			return json(['code' => -1, 'msg' => '用户名或密码错误!']);
		} catch (ValidateException $e) {
			return json(['code' => -1, 'msg' => $e->getError()]);
		} catch (\Exception $e) {
			return json(['code' => -1, 'msg' => $e->getMessage()]);
		}
	}
	
	// 注册
	public function register()
	{
		if(Session::has('admin_id')){
			return redirect((string) url('admin-index'));
		}
		
		return View::fetch('register');
	}
	
	//忘记密码
	public function forget()
	{
		if(Session::has('admin_id')){
			return redirect((string) url('admin-index'));
		}
		
		return View::fetch('forget');
	}
	
	
}