<?php
declare(strict_types=1);

namespace app\middleware;

use taoser\think\Auth as UserAuth;
use think\facade\Session;

class Auth
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
		$path = app('http')->getName().'/'.stristr($request->pathinfo(),".html",true);

		//没有登录及当前非登录页重定向登录页
		if(!Session::has('admin_id') && $path !== 'admin/login/index' && !stristr($request->pathinfo(),"captcha.html") )
		{
			return redirect((string) url('admin/login/index'));
		}
		
		//登陆后无法访问登录页
		if(Session::has('admin_id') && $path == 'admin/login/index'){
			return redirect((string) url('admin/index/index'));
		}
		
		// 排除公共权限
		$not_check = ['admin/','admin/login/index','admin/index/index','admin/index/home','admin/Admin/info','admin/Admin/repass','admin/Admin/logout','admin/Index/news','admin/Index/cunsult','admin/Index/replys','admin/Index/reply','admin/captcha'];

		if (!in_array($path, $not_check)) {
			$auth     = new UserAuth();
			$admin_id = Session::get('admin_id');	//登录用户的id

			if (!$auth->check($path, $admin_id) && $admin_id != 1) {
				return view('public/auth');
			}
		}
	return $next($request);	
    }
}
