<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-04-22 06:24:03
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \TaoLer\app\middleware\Auth.php
 * Copyright (c) 2020~2026 http://www.aieok.com All rights reserved.
 */
declare(strict_types=1);

namespace app\middleware;

use think\facade\Lang;
use think\facade\View;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Config;
use think\facade\Request;
use think\Response;
use taoser\think\Auth as UserAuth;


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
        // if(empty($request->param('sec')) || $request->param('sec') != 100){
        //     return response('403','403','text/plain');
        // }

        // 加载语言包
        Lang::load([
            app_path() . 'admin/lang/zh-cn.php',
            app_path() . 'admin/lang/en-us.php',
            app_path() . 'admin/lang/zh-tw.php',
        ]);

        // 配置视图路径
        View::config([
            'view_path'     => app_path() . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
            'view_dir_name' => 'view'
        ]);

        //    var_dump(Request::url(),Request::pathinfo(),$request->baseUrl(),$request->controller(), $request->action());
        $path = $request->pathinfo();
		//访问路径
        $path = str_contains($path, '.html') ? stristr($path, ".html",true) : $path;

        //    var_dump($path);
		
        //登陆前获取加密的Cookie
        $cooAuth = Cookie::get('adminAuth');

        if(!Session::has('admin_id')) {
            if(empty($cooAuth)){
                //没有登录及当前非登录页重定向登录页
                if(!in_array($path, ['login/index','login/register','admin/login'])) {
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
                'admin/login',
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
