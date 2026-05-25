<?php

use think\facade\Route;

Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');

Route::group('api')
    ->namespace('app\api\controller');

    Route::get('addons/sign/index/status','\\addons\\sign\\controller\\Index@status');
    
    // Route::get('addons/sign/index/signJson','Index/signJson');

// Route::group('addons')
    // ->namespace('addons\sign\controller');

// 注册模块分组子目录路由