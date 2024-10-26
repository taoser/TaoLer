<?php

namespace app\middleware;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Config;

class LoginCookie
{
    public function handle($request, \Closure $next)
    {
		//登陆前获取加密的Cookie
		$cooAuth = Cookie::get('auth');

		if(!session('?user_id') && !empty($cooAuth)){
			$resArr = explode(':',$cooAuth);
			$userId = end($resArr);
			//检验用户
			$user = Db::name('user')->where('id',$userId)->find();
			if(!is_null($user)){
				//验证cookie
				$salt = Config::get('taoler.salt');
				$auth = md5($user['name'].$salt).":".$userId;
				if($auth == $cooAuth){
					Session::set('user_name',$user['name']);
					Session::set('user_id',$userId);
				}
			}
		}
		
		return $next($request);
    }
}
