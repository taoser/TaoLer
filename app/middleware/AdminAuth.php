<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-04-22 06:24:03
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \TaoLer\app\middleware\Auth.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */
declare(strict_types=1);

namespace app\middleware;

use taoser\think\Auth as UserAuth;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Config;
use think\facade\Request;

class AdminAuth
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
//        var_dump(Request::url(),Request::pathinfo(),$request->baseUrl(),$request->controller());
		//访问路径
//		$path = app('http')->getName().'/'.stristr($request->pathinfo(),".html",true);
        $path = stristr($request->pathinfo(),".html",true) ?: Request::pathinfo();
//    var_dump($path);
		//登陆前获取加密的Cookie
		$cooAuth = Cookie::get('adminAuth');

		if(!session('?admin_id') && !empty($cooAuth)){
			$resArr = explode(':',$cooAuth);
			$userId = end($resArr);
			//检验用户
			$user = Db::name('admin')->where('id',$userId)->find();
			if(!is_null($user)){
				//验证cookie
				$salt = Config::get('taoler.salt');
				$auth = md5($user['username'].$salt).":".$userId;
				if($auth == $cooAuth){
					Session::set('admin_name',$user['username']);
					Session::set('admin_id',$userId);
				}
			}
			
		}

//		//没有登录及当前非登录页重定向登录页
//		if(!Session::has('admin_id') && $path !== 'admin/login/index' && !(stristr($request->pathinfo(),"captcha.html") || stristr($request->pathinfo(),"addons")) )
//		{
//			return redirect((string) url('login/index'));
//		}
//		//登陆后无法访问登录页
//		if(Session::has('admin_id') && $path == 'admin/login/index'){
//			return redirect((string) url('index/index'));
//		}
//		// 排除公共权限
//		$not_check = ['admin/','index/index', 'admin/menu/getMenuNavbar','admin/login/index','admin/index/index','admin/index/home','admin/Admin/info','admin/Admin/repass','admin/Admin/logout','admin/Index/news','admin/Index/cunsult','admin/Index/replys','admin/Index/reply','admin/captcha','addons/socail/','admin/addons/social/oauth/login','admin/addons/bacimg/index/getImages'];


        //没有登录及当前非登录页重定向登录页
        if(!Session::has('admin_id') && $path !== 'login/index' && !(stristr($request->pathinfo(),"captcha.html") || stristr($request->pathinfo(),"addons")) )
        {
            return redirect((string) url('login/index'));
        }
        //登陆后无法访问登录页
        if(Session::has('admin_id') && $path == 'login/index' || $path == ''){
            return redirect((string) url('index/index'));
        }

        // 排除公共权限
        $not_check = [
            'captcha',
            'login/index',
            'admin/index',
            'system.menu/getnav',
            'index/index',
            'index/console1',
            'index/console2',
            'index/news',
            'menu/getMenuNavbar',
            'index/home',
            'Admin/info',
            'system.admin/repass',
            'system.admin/logout',
            'Index/cunsult',
            'Index/replys',
            'Index/reply',
            'admin/captcha',
            'addons/socail/',
            'addons/social/oauth/login',
            'addons/bacimg/index/getImages'
        ];

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
