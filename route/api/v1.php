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
Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');

Route::group('v1',function(){
	//测试
	Route::rule('index','Index/index');
	Route::rule('cy','Index/cy');
	//升级
	Route::rule('upload/check','Upload/check');
	Route::rule('upload/api','Upload/api');
	Route::rule('upload/appupdate','Upload/appUpcheck');
	//动态和反馈
	Route::get('news','News/index')->allowCrossDomain();
	Route::post('reply','News/reply');
	Route::post('handler','File/handler')->allowCrossDomain();
	Route::rule('pay','Pay/index');
	Route::rule('addons','Addons/index')->allowCrossDomain();
	Route::rule('addonlist','Addons/addonList')->allowCrossDomain();
	Route::rule('getaddons','Addons/getAddons')->allowCrossDomain();
	Route::post('createOrder','Addons/createOrder')->allowCrossDomain();
	Route::post('ispay','Addons/isPay')->allowCrossDomain();
	Route::post('mailserver','MailServer/index')->allowCrossDomain();

	// 模板
	Route::group('template', function() {
		Route::post('index', 'index');
		Route::post('install', 'install');
		Route::post('update', 'update');
	})->prefix('Template/');

})->prefix('v1.')->allowCrossDomain();

// 用户
Route::group('v1/user', function () {
    Route::post('login','v1.User/login');
	Route::post('login_api','v1.User/loginApi');
	Route::post('getinfo', 'v1.User/getUserInfo')->middleware(\app\middleware\Auth::class);
	Route::put('setinfo', 'v1.User/setUserInfo')->middleware(\app\middleware\Auth::class);
	Route::post('upavatar', 'v1.User/uploadAvatar')->middleware(\app\middleware\Auth::class);
})->allowCrossDomain();

Route::post('v1/reptiles/addserver','v1.Reptiles/addServer')->allowCrossDomain();
Route::post('v1/reptiles/start','v1.Reptiles/start')->allowCrossDomain();
Route::post('v1/reptiles/server','v1.Reptiles/server')->allowCrossDomain();

Route::post('v1/order/ispay','v1.Order/isPay')->allowCrossDomain();
Route::post('v1/order/createorder','v1.Order/createOrder')->allowCrossDomain();

// sms
Route::post('v1/sms/send','v1.Sms/sendMsg')->allowCrossDomain();
Route::post('v1/sms/verify','v1.Sms/verifyCode')->allowCrossDomain();

Route::get('v1/area/city', 'v1.Area/getCity')->allowCrossDomain();






