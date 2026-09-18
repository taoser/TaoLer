<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-07-30 07:19:59
 * @LastEditTime: 2026-09-18 19:44:44
 * @LastEditors: TaoLer
 * @Description: 已登录检查中间件
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\middleware\LogedCheck.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\middleware;
use think\facade\Session;

class LogedCheck
{
    public function handle($request, \Closure $next)
    {
		//已登陆跳出
		if(Session::has('user_id')){
			return redirect((string) url('user_page'));
		}
		return $next($request);
    }
}
