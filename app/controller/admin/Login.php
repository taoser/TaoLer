<?php
/**
 * @Program: TaoLer 2023/3/11
 * @FilePath: app\admin\controller\login.php
 * @Description: 管理后台登录页
 * @LastEditTime: 2024-11-14 16:37:42
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\controller\admin;

use app\controller\admin\AdminBaseController;
use Exception;
use think\facade\View;
use think\facade\Request;
use think\facade\Session;
use app\validate\admin\Admin;
use think\exception\ValidateException;

class Login extends AdminBaseController
{
	
	public function index()
	{	
		return View::fetch('index');
	}

	// 登录
	public function signin()
	{
		// halt(session('admin'));
		if(Request::isAjax()){
			$data = Request::only(['username', 'password', 'captcha','remember']);

				validate(Admin::class)
				->scene('Login')
				->check($data);

				$user = new \app\model\admin\Admin();
				$result = $user->login($data);
				try{
			} catch(ValidateException $v){
				return json(['code' => -1,'msg' => $v->getError()]);
			} catch(Exception $e) {
				return json(['code' => -1,'msg' => $e->getMessage()]);
			}

			return json([
				'code' => 0,
				'msg' => '登陆成功',
				'data' => [
					'token' => isset($result['data']['token']) ? $result['data']['token'] : '',
					'url' => (string) url('admin_index')
				]
			]);
		}
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