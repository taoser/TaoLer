<?php

namespace app\middleware;
use think\facade\Session;

class LoginCheck
{
    public function handle($request, \Closure $next)
    {
		//需要登陆的操作
		if(Session::has('user_id')){
			return $next($request);
		} else {
			return redirect((string) url('login/index'));
		}
    }
}
