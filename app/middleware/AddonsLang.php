<?php

namespace app\middleware;
use think\facade\Request;
use think\facade\Lang;

class AddonsLang
{
    public function handle($request, \Closure $next)
    {
		Lang::load([
            //$this->app->getRootPath() . '/vendor/zzstudio/think-addons/src/lang/zh-cn.php',
			app()->getRootPath().'addons/sign/lang/zh-cn.php',
        ]);
		
		return $next($request);
	}
}
