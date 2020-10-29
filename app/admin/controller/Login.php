<?php
namespace app\admin\controller;

use think\facade\View;
use think\facade\Request;
use think\facade\Session;
use app\admin\validate\Admin;
use think\exception\ValidateException;


class Login
{
	//登录
	public function index()
	{
		
		if(Session::has('admin_id')){
			return redirect((string) url('index/index'));
		}
			
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
			$res = $user->login($data);

			if ($res == 1) {
				$res = ['code'=>0,'msg'=>'登陆成功', 'url'=>(string) url('index/index')];
				//$res['data']['access_token'] = $data['__token__'];
			} else {
				$res = ['code'=>-1,'msg'=>$res,'url'=>'admin/login'];
			}
			return json($res);
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