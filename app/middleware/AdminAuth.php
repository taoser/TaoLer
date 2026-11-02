<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2026-11-02 17:37:45
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

use app\entity\Admin;

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

        // var_dump($controller, $action);
  
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
        
        // 未登录
        if (!$isLogin) {
            // 未登录，访问公共页面 → 放行
            if ($isPublic) {
                return $next($request);
            } 
            // 未登录，访问非公共页面 → 跳登录
            return redirect((string)url('admin-login'));
        }

        // 已登录，访问公共页面 → 跳后台首页
        if ($isPublic) {
            return redirect((string)url('admin-index'));
        }

        $adminId = Session::get('admin.id');
        
        $admin = Admin::find($adminId);
       
        if (!$admin || $admin->status != 1) {
            return json(['code' => 401, 'msg' => '用户不存在或已禁用']);
        }

        // 白名单路由
        $whiteList = [
            // 管理主页
            'index/index',
            'index/console1',
            'index/console2',
            // 网站配置
            'system/service/siteConfig',
            // 权限菜单
            'system/Service/getMenu',
            // 个人权限按钮组
            'system/admin/getPermission',
            // 更新管理员信息
            'system/admin/setInfo',
            // 重置密码
            'system/admin/newPassword',
            // 退出登录
            'system/admin/logout',

        ];

        // 检查权限
        if (!in_array($path, array_map('strtolower', $whiteList)) ) {
            if (!$admin->hasPerm($path)) {
                return response("<script>alert('没有操作权限')</script>");
            }
        }

        // 将管理员信息和ID存入请求对象，方便后续使用
        $request->aid = $adminId;
        $request->admin = $admin;
        
		return $next($request);	
    }
}
