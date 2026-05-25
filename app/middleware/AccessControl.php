<?php
namespace app\middleware;

use app\common\lib\JwtAuth;

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
    public function handle($request, \Closure $next)
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

        $path = $request->pathinfo();


        if(str_starts_with($path, 'addons/')) {

            $pathArr = explode("/", str_replace('.html','', str_replace('\\','/',$path)));

            $addon = $pathArr[1];
            $controller = $pathArr[2];
            $action = $pathArr[3];

            // -------------反射
            $className = ucwords($controller);
            $controllerClass = "\\addons\\{$addon}\\controller\\{$className}";

            $class = new \ReflectionClass ($controllerClass);
            $properties = $class->getDefaultProperties();

            $noNeedLogin = $properties['noNeedLogin'] ?? [];
            $noNeedAuth = $properties['noNeedAuth'] ?? [];

            // 不需要登录
            if (!in_array($action, $noNeedLogin)) {
                if(!session('?user_id')){
                    return redirect((string) url('index/user_login'));
                }
            }

            // 不需要鉴权
            if (!in_array($action, $noNeedAuth)) {
                //
            }
            
            $request->uid = (int) session('user_id');
        }

        return $next($request);
    }

}