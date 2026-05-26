<?php
namespace app\middleware;

use think\Request;

class AccessControl
{
    public function handle(Request $request, \Closure $next)
    {
		
		
		return $next($request);
	}
}