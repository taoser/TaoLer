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
        // var_dump(Request::url(),Request::pathinfo(),$request->baseUrl(),$request->controller(),$request->action());

        $controller = $request->controller();
        $action = $request->action();

        $path = strtolower($controller . '/' . $action);

        // 1.未登录状态
        if(!Session::has('admin')) {
            // 未登录时，访问权限页面跳转登录页，只能放行访问 登录，注册，找回密码，提交注册
            if(!in_array($path, ['admin.login/index','admin.login/reg','admin.login/forget','admin.login/signin'])) {
                return redirect((string)url('admin_login'));
            }

            return $next($request);
        }

        // 2.已登录 禁止访问 登录，注册，找回密码，提交注册
        if(in_array($path, ['admin.login/index','admin.login/reg','admin.login/forget','admin.login/signin'])) {
            return redirect((string)url('admin_index'));
        }

        // 3.管理用户信息
        $admin = Session::get('admin');
        $aid = (int) $admin['id'];
        $request->aid = $aid; // 控制器传参

        // 4.非超级管理员鉴权开始
        if($aid !== 1) {
            // 公共权限
            $not_check_list = [
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
                'Index/reply'
            ];

            // 未在排除列表中的地址，需要鉴权
            if(!in_array($path, $not_check_list)) {
                
                $auth = new UserAuth();
                if (!$auth->check($path, $admin['id'])) {
                    //return view('public/auth');
                    return response("<script>alert('没有操作权限')</script>");
                }
            }
            
        }

		return $next($request);	
    }
}
