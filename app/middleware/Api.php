<?php
namespace app\middleware;

use think\facade\Request;
use think\facade\Route;
use think\exception\ClassNotFoundException;
use think\exception\MethodNotFoundException;
use think\exception\ControllerNotFoundException;

class Api
{
    public function handle($request, \Closure $next)
    {
        // 允许跨域请求
        // header('Access-Control-Allow-Origin: *');
        // header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        // header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // // 如果是预检请求，直接返回成功响应
        // if ($request->isOptions()) {
        //     return response('', 200);
        // }

        try {
            $response = $next($request);
        } catch (ClassNotFoundException $e) {
            return response($e->getMessage(), 404);
        }

        return $response;
    }
}   
