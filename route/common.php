<?php


use think\facade\Route;

Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');

Route::group('install', function () {
    Route::get('/', 'index/index');
    Route::post('index/start$', 'index/start');
})
->namespace('app\install\controller')
->middleware(\app\install\middleware\InstallCheck::class)
;

// 开启多模块URL自动解析 `8.1+`版本开始支持
Route::auto();

Route::miss(function() {
	return '404 Not Found!';
});

// 查看已注册的路由
