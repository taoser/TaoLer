<?php


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



