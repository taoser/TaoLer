<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-18 21:58:05
 * @LastEditors: TaoLer
 * @Description: 公共路由
 * @Version: V4.0.0
 * @FilePath: \TaoLer\route\common.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */


use think\facade\Route;

// 验证码路由
Route::group('captcha',function(){
    Route::get('[:id]', 'CaptchaController/index');
})->namespace('think\captcha');

// 安装路由
Route::group('install', function () {
    Route::get('/', 'index/index');
    Route::post('index/start$', 'index/start');
})
->namespace('app\install\controller')
->middleware(\app\install\middleware\InstallCheck::class);

// api路由
Route::group('api')->namespace('app\api\controller');



