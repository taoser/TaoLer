<?php

namespace app\middleware;
use think\facade\Log;

class Browse
{
    public function handle($request, \Closure $next)
    {
		$agent = $request->header('user-agent');
		$ip = $request->ip();
		$url = $request->url(true);
		Log::channel('browse')->info('browse:{agent} {ip} {url}',['agent'=>$agent,'url'=>$url,'ip'=>$ip]);
		
		return $next($request);
	}
}
