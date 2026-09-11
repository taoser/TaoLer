<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-11 18:38:48
 * @LastEditors: TaoLer
 * @Description: 插件访问控制中间件
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\middleware\AccessControl.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\middleware;

use think\Request;
use think\exception\HttpException;
use app\common\helper\JwtAuth;

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

        try {

            // 处理路由参数 有2种模式，一种是通过路由参数，一种是通过layer参数
            $layer = $request->layer();

            if(empty($layer)) {
                // 默认路由模式
                $addon = $request->route('addon');
                $controller = $request->route('controller');
                $action = $request->route('action');
            } else {
                // 自定义路由模式
                $addon = basename($layer);
                $controller = $request->controller();
                $action = $request->action();
            }

            if (empty($addon) || empty($controller) || empty($action)) {
                throw new HttpException(500, lang('路由地址错误'));
            }

            // var_dump($addon, $controller, $action);

            // -------------反射
            $className = ucwords($controller);
            $controllerClass = "\\addons\\{$addon}\\controller\\{$className}";

            $class = new \ReflectionClass($controllerClass);
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
                    return json(['code' => -1, 'msg' => '请先登录']);        
                }
            }

            // 不需要鉴权
            if (!in_array($action, $noNeedAuth)) {
                //
            }
            
            $request->uid = (int) session('user_id');

            return $next($request);
        
        } catch(\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }

    }

}