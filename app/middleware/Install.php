<?php

namespace app\middleware;
use think\facade\Request;

class Install
{
    public function handle($request, \Closure $next)
    {
		if(!file_exists('../install.lock')){
			return redirect('/install/index');
			//header('Location:'.Request::domain().'/install.php');
		}
			return $next($request);
	}
}
