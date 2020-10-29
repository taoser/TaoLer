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
		$response = $next($request);
		//后置中间件获取应用控制器方法
		$app = app('http')->getName();
		$controller = $request->controller();
		$action     = $request->action();

		//登陆后检验权限
		if (Session::has('admin_id')) {
			// 排除权限
			$not_check = ['admin/Login/index','admin/Index/index','admin/Index/home','admin/Set/info','admin/Set/password','admin/User/logout'];

			if (!in_array($app . '/' . $controller . '/' . $action, $not_check)) {
				$auth     = new UserAuth();
				$admin_id = Session::get('admin_id');	//登录用户的id

				if (!$auth->check($app . '/' . $controller . '/' . $action, $admin_id) && $admin_id != 1) {
					//return json(['code'=>-1,'msg'=>'没有权限!']);
					return view('public/auth');
				}
			}
			
		} else {
			//排除登录页和验证码及退出登录后被重定向
			if($controller !== 'Login' && !stristr($request->pathinfo(),"captcha.html") && $action !== 'logout')
			{
				//非登录重定向
				return redirect((string) url('admin/login/index'));
			}		
		} 
	return $response;	
    }
}
