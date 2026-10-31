<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2026-10-31 12:35:28
 * @LastEditors: TaoLer
 * @Description: 管理员认证中间件
 * @FilePath: \TaoLer\app\middleware\AdminAuth.php
 * Copyright (c) 2020~2026 http://www.aieok.com All rights reserved.
 */
declare(strict_types=1);

namespace app\middleware;

use think\Request;
use think\Response;
use think\facade\Lang;
use think\facade\View;
use think\facade\Session;
use think\facade\Config;
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
        // admin模块名
        $adminModuleName = '/' . trim(system_config('admin_module', 'admin'), '/');

        $controller = $request->controller();
        $action = $request->action();
  
        View::assign('moduleName', $adminModuleName);

        $path = strtolower(trim($controller) . '/' . strtolower($action));

        // 默认语言
        // $lang = Config::get('lang.default_lang');
        // // $langArr = glob(app_path().'admin/lang/*.php');
		// $file = app_path().'admin/lang/'.$lang.'.php';
        // // 加载语言包
        // Lang::load($file);

        // 切换语言
        $currentLang = system_config('current_lang', 'en-us');
        $lang = strtolower($currentLang);
        Lang::switchLangSet($lang);

        // 配置视图路径
        View::config([
            'view_path'     => app_path() . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
            'view_dir_name' => 'view'
        ]);

        // 【公共免登录路由】已登录不能访问这些
        $publicActions = [
            'login/index',
            'login/register',
            'login/forget',
        ];

        // dump(session('admin'));

        $isLogin = Session::has('admin');
        $isPublic = in_array($path, $publicActions);

        // 未登录，且不是公共页面 → 跳登录
        if (!$isLogin && !$isPublic) {
            return redirect((string)url('admin-login'));
        }
        // 已登录，访问公共页面 → 跳后台首页
        if ($isLogin && $isPublic) {
            return redirect((string)url('admin-index'));
        }

        $admin_id = Session::get('admin.id');
        $request->aid = $admin_id;

        if($admin_id !==1) {
            // 排除公共权限
            $not_check_list = [
                'admin/login',
                'login/index',
                'login/register',
                'admin/index',
                'system/menu/getnav',
                'index/index',
                'index/console1',
                'index/console2',
                'index/news',
                'menu/getmenunavbar',
                'index/home',
                'admin/info',
                'system/admin/repass',
                'system/admin/logout',
                'system/admin/setInfo',
                'system/menu/getmenujsondata',
                'index/cunsult',
                'index/replys',
                'index/reply',
            ];

            if (!in_array($path, $not_check_list)) {
                $auth     = new UserAuth();
                if (!$auth->check($path, $admin_id)) {
                    return response("<script>alert('没有操作权限')</script>");
                }
            }
        }
        
		return $next($request);	
    }
}
