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

Route::namespace('app\api\controller\v1')
    ->pattern(['id' => '\d+']);

Route::group(function(){
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

})->allowCrossDomain();

Route::post('reptiles/addserver','Reptiles/addServer')->allowCrossDomain();
Route::post('reptiles/start','Reptiles/start')->allowCrossDomain();
Route::post('reptiles/server','Reptiles/server')->allowCrossDomain();

Route::post('order/ispay','Order/isPay')->allowCrossDomain();
Route::post('order/createorder','Order/createOrder')->allowCrossDomain();

// sms
Route::post('sms/send','Sms/sendMsg')->allowCrossDomain();
Route::post('sms/verify','Sms/verifyCode')->allowCrossDomain();

Route::get('area/city', 'Area/getCity')->allowCrossDomain();






