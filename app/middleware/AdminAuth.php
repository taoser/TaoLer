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
use think\Request;
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
        // admin模块名称
        $adminModuleName = Config::get('taoler.admin_module_name');
        $controller = $request->controller();
        $action = $request->action();
        // var_dump($controller,$action);

        $path = strtolower($controller) . '/' . strtolower($action);

        // 默认语言
        // $lang = Config::get('lang.default_lang');
        // // $langArr = glob(app_path().'admin/lang/*.php');
		// $file = app_path().'admin/lang/'.$lang.'.php';
        // // 加载语言包
        // Lang::load($file);

        // 配置视图路径
        View::config([
            'view_path'     => app_path() . 'admin' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
            'view_dir_name' => 'view'
        ]);
           
        //登陆前获取加密的Cookie
        $cooAuth = Cookie::get('adminAuth');
        // 没有登录
        if(!Session::has('admin_id')) {
            if(empty($cooAuth)){

                //没有登录 当前非登录页重定向登录页
                if(!in_array($path, ['login/index','login/register','admin/login', $adminModuleName, $adminModuleName.'/index'])) {
                    return redirect((string) url('admin-login'));
                    return false;
                    // $adminModuleName, $adminModuleName.'/index'
                }

            } else {
                
                $resArr = explode(':', $cooAuth);
                $userId = end($resArr);
      
                //检验用户
                $user = Db::name('admin')->where('id', $userId)->find();

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
        
        // 已登陆
        // 登陆后无法访问登录页、忘记密码页、注册页
        if(Session::has('admin_id')){
            if(in_array($path, ['login/index','login/register','register/index'])){
                return redirect((string) url('admin-index'));
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
                'system/admin/infoSet',
                'system/menu/getmenujsondata',
                'index/cunsult',
                'index/replys',
                'index/reply',
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
