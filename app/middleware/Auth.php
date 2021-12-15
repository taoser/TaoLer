<?php
declare(strict_types=1);

namespace app\middleware;

use taoser\think\Auth as UserAuth;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Config;

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
		//访问路径
		$path = app('http')->getName().'/'.stristr($request->pathinfo(),".html",true);
		//登陆前获取加密的Cookie
		$cooAuth = Cookie::get('adminAuth');

		if(!empty($cooAuth)){
			$resArr = explode(':',$cooAuth);
			$userId = end($resArr);
			//检验用户
			$user = Db::name('admin')->where('id',$userId)->find();
			if(!empty($user)){
				//验证cookie
				$salt = Config::get('taoler.salt');
				$auth = md5($user['username'].$salt).":".$userId;
				if($auth==$cooAuth){
					Session::set('admin_name',$user['username']);
					Session::set('admin_id',$userId);
				}
			}
			
		}

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
		$not_check = ['admin/','admin/login/index','admin/index/index','admin/index/home','admin/Admin/info','admin/Admin/repass','admin/Admin/logout','admin/Index/news','admin/Index/cunsult','admin/Index/replys','admin/Index/reply','admin/captcha','addons/socail/'];

		if (!in_array($path, $not_check)) {
			$auth     = new UserAuth();
			$admin_id = Session::get('admin_id');	//登录用户的id

			if (!$auth->check($path, $admin_id) && $admin_id != 1) {
				//return view('public/auth');
				//return response("<script>alert('没有操作权限')</script>");
				return json(['code'=>-1,'msg'=>'无权限']);
			}
		}
		return $next($request);	
    }
}
