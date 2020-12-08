<?php

namespace app\middleware;
use think\facade\Session;

class LogedCheck
{
    public function handle($request, \Closure $next)
    {
		//已登陆跳出
		if(Session::has('user_id')){
			return redirect((string) url('user/index'));
		}
		return $next($request);
    }
}
