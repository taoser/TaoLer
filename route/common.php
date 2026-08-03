<?php


use think\facade\Route;

Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');

Route::group('install', function () {
    Route::get('/', 'index/index');
    Route::post('index/start$', 'index/start');
})
->namespace('app\install\controller')
->middleware(\app\install\middleware\InstallCheck::class);

Route::group('api')
    ->namespace('app\api\controller');
