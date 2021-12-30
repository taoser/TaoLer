<?php
namespace app\admin\controller;

use think\facade\View;
use think\facade\Request;
use think\facade\Session;
use app\admin\validate\Admin;
use think\exception\ValidateException;
use app\common\controller\AdminController;


class Login extends AdminController
{
	//登录
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
			return redirect('/admin/index/index');
		}
		
		return View::fetch('reg');
	}
	
	//忘记密码
	public function forget()
	{
		if(Session::has('admin_id')){
			return redirect('/admin/index/index');
		}
		
		return View::fetch('forget');
	}
	
	
}