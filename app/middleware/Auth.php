<?php

declare(strict_types=1);

namespace app\middleware;

use Exception;
use think\Request;
use app\common\helper\JwtAuth;


class Auth
{
	public function handle(Request $request, \Closure $next)
	{
		$header = $request->header();

		try {
			// 从请求头获取令牌
			$token = JwtAuth::getHeaderToken($header);
			// 验证令牌
			$userData = JwtAuth::verify($token);
			// 从解码后的数据中获取用户ID
			$request->uid = $userData->uid;
			return $next($request);
		} catch (Exception $e) {
			return json(['code' => 401, 'msg' => $e->getMessage()]);
		}
	}
}
