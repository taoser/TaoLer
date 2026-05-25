<?php
namespace app\middleware;

class AccessControl
{
    public function handle($request, \Closure $next)
    {
		
		
		return $next($request);
	}
}