<?php

namespace app\middleware;

use app\common\lib\JwtAuth;
use think\facade\Session;

class StaticFile
{
    public function handle($request, \Closure $next)
    {
		//访问路径
        $path = str_contains($request->pathinfo(), 'licence_pic');
        if($path) {
            if(!Session::has('admin_id')) {
                return json(['code' => 0, 'msg' => 'no auth']);
            }
        }
        return $next($request);
    }
}
