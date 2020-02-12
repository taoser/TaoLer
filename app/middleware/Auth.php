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
		//halt($request->pathinfo());
		$response = $next($request);

		if($request->controller() !== 'Login' && $request->action() !=='logout')
		{
			//登陆检验
			if (!Session::has('admin_id')) {
				return redirect('/admin/login/index');
			} 
			$app = app('http')->getName();
			$controller = $request->controller();
			$action     = $request->action();

			// 排除权限
			$not_check = ['admin/Index/index','admin/Index/home','admin/Set/info','admin/Set/password','admin/User/logout'];

			if (!in_array($app . '/' . $controller . '/' . $action, $not_check)) {
				$auth     = new UserAuth();
				$admin_id = Session::get('admin_id');

				if (!$auth->check($app . '/' . $controller . '/' . $action, $admin_id) && $admin_id != 1) {
					//return response('<script>alert("没有权限");location.back()</script>');
					return response('没有权限');
				}
			}
		}

		return $response;
    }
	
	
}
