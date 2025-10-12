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

use PgSql\Result;
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
    //    var_dump(Request::url(),Request::pathinfo(),$request->baseUrl(),$request->controller(), $request->action());

		//访问路径
        $path = str_contains($request->pathinfo(), '.html') ? stristr($request->pathinfo(), ".html",true) : $request->pathinfo();

//    var_dump($path);
		
         //登陆前获取加密的Cookie
         $cooAuth = Cookie::get('adminAuth');

         if(!Session::has('admin_id')) {
            if(empty($cooAuth)){
                //没有登录及当前非登录页重定向登录页
                if(!in_array($path, ['login/index','login/register'])) {
                    return redirect((string) url('login/index'));
                }

            } else {
                
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

         }
        
        //登陆后无法访问登录页
        if(Session::has('admin_id')){
            if(in_array($path, ['login/index','login/register'])){
                return redirect((string) url('index/index'));
            }
        }

        $admin_id = (int) Session::get('admin_id');
        $request->aid = $admin_id;

        if($admin_id !==1) {
            // 排除公共权限
            $not_check_list = [
                'login/index',
                'login/register',
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
                'system.menu/getMenuJsonData',
                'Index/cunsult',
                'Index/replys',
                'Index/reply',
            ];

            if (!in_array($path, $not_check_list)) {
                $auth     = new UserAuth();
                if (!$auth->check($path, $admin_id)) {
                    //return view('public/auth');
                    return response("<script>alert('没有操作权限')</script>");
                }
            }
        }
        
		return $next($request);	
    }
}
