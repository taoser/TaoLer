<?php

namespace app\middleware;

use app\common\lib\JwtAuth;

class Auth
{
    public function handle($request, \Closure $next)
    {
		$header = $request->header();

		if(!isset($header['authorization'])) {
			return json(['code' => -1, 'msg' => 'no auth']);
		}
			
		$token = trim(ltrim($request->header('authorization'), 'Bearer'));

		try{
			$data = JwtAuth::decode($token);
			
			$request->uid = $data->uid;
			
		} catch(\Exception $e) {
			return json(['code' => -1, 'msg' => $e->getMessage()]);
		}
			
		return $next($request);
    }
}
