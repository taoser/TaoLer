<?php

namespace app\middleware;

use app\common\lib\JwtAuth;

class Auth
{
    public function handle($request, \Closure $next)
    {
		$header = $request->header();

		if(isset($header['authorization'])) {
			$token = trim(ltrim($request->header('authorization'), 'Bearer'));
			
			try{
				$data = JwtAuth::decode($token);
				
				$request->uid = $data->uid;
				
			} catch(\Exception $e) {
				return $e->getMessage();
			}
			
		} else {
			return json(['code' => -1, 'msg' => 'no auth']);
		}
		//登陆前获取加密的Cookie
			
		return $next($request);
    }
}
