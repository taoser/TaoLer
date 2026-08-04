<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\console\output\descriptor\Console;
use think\facade\Route;

// Route::namespace('app\api\controller\v1')
//     ->pattern(['id' => '\d+']);

// 用户
Route::group('user',function(){
    Route::get('index','user/index'); // 测试路由
	Route::post('login','User/login');
	Route::post('login_api','User/loginApi');
	Route::post('getinfo', 'User/getUserInfo')->middleware(\app\middleware\Auth::class);
	Route::put('setinfo', 'User/setUserInfo')->middleware(\app\middleware\Auth::class);
	Route::post('upavatar', 'User/uploadAvatar')->middleware(\app\middleware\Auth::class);
});










