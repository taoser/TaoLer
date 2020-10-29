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
use think\facade\Route;

Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');
Route::rule('/', 'index'); // 首页访问路由
Route::group(function () {
	Route::get('jie/:id', 'article/detail'); 
	Route::get('column/<ename?>/<type?>/<page?>','article/cate');
	Route::rule('add','article/add');
	Route::rule('edit/[:id]','article/edit');
	//Route::rule('del/:id','article/delete');
});
Route::group(function () {
	Route::rule('u/:id', 'user/home'); 	
});
Route::rule('login','login/index');
Route::rule('forget','login/forget');
Route::rule('postcode','login/postcode');
Route::rule('respass','login/respass');
Route::rule('reg','Login/reg')
	->middleware(\app\middleware\CheckRegister::class);
	
Route::rule('search/[:keywords]', 'index/search'); // 搜索
	