<?php

namespace app\middleware;
use think\facade\Request;
use think\facade\Log;

class Browse
{
    public function handle($request, \Closure $next)
    {
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$ip = $_SERVER['REMOTE_ADDR'];
		$url = Request::url(true);
		Log::channel('browse')->info('browse:{agent} {ip} {url}',['agent'=>$agent,'url'=>$url,'ip'=>$ip]);
			return $next($request);
	}
}
