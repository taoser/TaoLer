<?php

namespace app\middleware;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Config;

class AdminLoginCookie
{
    public function handle($request, \Closure $next)
    {
		//登陆前获取加密的Cookie
			$cooAuth = Cookie::get('adminAuth');
		if(!empty($cooAuth)){
			$resArr = explode(':',$cooAuth);
			$userId = end($resArr);
			//检验用户
			$user = Db::name('admin')->where('id',$userId)->find();
			if(!is_null($user)){
				//验证cookie
				$salt = Config::get('taoler.salt');
				$auth = md5($user['username'].$salt).":".$userId;
				if($auth==$cooAuth){
					Session::set('admin_name',$user['username']);
					Session::set('admin_id',$userId);
				}
			}
			
		}
		return $next($request);
    }
}
