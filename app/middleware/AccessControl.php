<?php
namespace app\middleware;

use think\Request;
use think\facade\Route;
use think\exception\HttpException;
use app\common\helper\JwtAuth;

/**
 * @Program: table.css 2024/6/2
 * @FilePath: ${NAMESPACE}\AccessControl.php
 * @Description: AccessControl.php
 * @LastEditTime: 2024-06-02 11:52:04
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

class AccessControl
{
    public function handle(Request $request, \Closure $next)
    {
//        $header = $request->header();
//
//        if(isset($header['authorization'])) {
//            $token = trim(ltrim($request->header('authorization'), 'Bearer'));
//
//            try{
//                $data = JwtAuth::decode($token);
//
//                $request->uid = $data->uid;
//
//            } catch(\Exception $e) {
//                return json(['code' => -1, 'msg' => $e->getMessage()]);
//            }
//
//        } else {
//            return json(['code' => -1, 'msg' => 'no auth']);
//        }

        // try{

            $path = $request->pathinfo();

            $prefix = config('route.url_html_suffix');
            if(!empty($prefix)) {
                $path = str_replace('.'.$prefix, '', $path);
            }

            // 自定义路由适配
            $rule = Route::getRule($path);

            var_dump($rule);

            if(!empty($rule)) {
                $keys = array_keys($rule);
                $str = $keys[0];
                $str = str_replace('\\', '/', $str);
                $str = str_replace('/addons/', '', $str);
                $str = str_replace('controller/', '', $str);
                var_dump($str);
                if(str_contains($str, '@')) {
                    [$addon, $s] = explode('/', $str);
                    [$controller, $action] = explode('@', $s);
                } else {
                    $parts = explode('/', $str);
                    $count = count($parts);
                    if($count == 3) {
                        $addon = $parts[0];
                        $controller = $parts[1];
                        $action = $parts[2];
                    }
                    if($count == 2) {
                        $controller = $parts[0];
                        $action = $parts[1];
                        $addon = 'sign';
                    }
                }

            } elseif (str_starts_with($path, 'app/')) { // 插件默认路由适配

                // 处理路由参数 有2种模式，一种是通过路由参数，一种是通过layer参数
                $layer = $request->layer();
                if(empty($layer)) {
                    $addon = $request->route('addon');
                    $controller = $request->route('controller');
                    $action = $request->route('action');
                } else {
                    $addon = basename($layer);
                    $controller = $request->controller();
                    $action = $request->action();
                }

                if (empty($addon) || empty($controller) || empty($action)) {
                    throw new HttpException(500, lang('路由地址错误'));
                }
            }

            var_dump($addon, $controller, $action);

            // -------------反射
            $className = ucwords($controller);
            $controllerClass = "\\addons\\{$addon}\\controller\\{$className}";

            $class = new \ReflectionClass ($controllerClass);
            $properties = $class->getDefaultProperties();

            $noNeedLogin = $properties['noNeedLogin'] ?? [];
            $noNeedAuth = $properties['noNeedAuth'] ?? [];
            // 变小写
            array_walk($noNeedLogin, function (&$item) {
                if (is_string($item)) {
                    $item = strtolower($item);
                }
            });
            
            // 不需要登录
            if (!in_array(strtolower($action), $noNeedLogin)) {
                if(!session('?user_id')){
                    // return redirect('/login');
                    return json(['code'=>-1,'msg'=>'请先登录']);        
                }
            }

            // 不需要鉴权
            if (!in_array($action, $noNeedAuth)) {
                //
            }
            
            $request->uid = (int) session('user_id');
        
        // } catch(\Exception $e) {
        //     return json(['code' => -1, 'msg' => $e->getMessage()]);
        // }

        return $next($request);
    }

    protected function getAddonsName(string $str)
    {
        $routeList = app('route')->getRuleList();

        foreach($routeList as $key => $item) {
            if($item[$key] == $str) {
                $prefix  = $item['options']['prefix'] ?? '';
            }
        }
        return 'sign';
    }

}