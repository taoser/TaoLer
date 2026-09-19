<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-18 19:43:33
 * @LastEditors: TaoLer
 * @Description: 登录检查中间件
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\middleware\LoginCheck.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\middleware;
use think\facade\Session;

class LoginCheck
{
    public function handle($request, \Closure $next)
    {
		//需要登陆的操作
		if(Session::has('user_id')){
			return $next($request);
		}

		return redirect((string) url('login_page'));
    }
}
