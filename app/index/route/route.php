<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-06-27 16:55:39
 * @LastEditors: TaoLer
 * @Description: 前端路由设置
 * @FilePath: \TaoLer\app\index\route\route.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */
use think\facade\Route;

//详情页URL别称
$detail_as = config('taoler.url_rewrite.article_as');
//分类别称
$cate_as = config('taoler.url_rewrite.cate_as');

Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');

Route::group(function () {
	Route::get('u/:id$', 'user/home')->name('user_home'); 	
});

Route::rule('login','login/index');
Route::rule('forget','login/forget');
Route::rule('postcode','login/postcode');
Route::rule('sentemailcode','login/sentMailCode');
Route::rule('respass','login/respass');
Route::rule('reg$','Login/reg')
	->middleware(\app\middleware\CheckRegister::class);
	
Route::get('logout', 'user/logout');

Route::rule('/', 'index'); // 首页访问路由

Route::group(function () use($detail_as,$cate_as){
	Route::rule('add','Article/add');
	Route::rule('tags','Article/tags')->allowCrossDomain();
	Route::rule('edit/[:id]','Article/edit');
	// 动态路径路由会影响下面的路由，所以动态路由放下面
	Route::get($detail_as . ':id$', 'article/detail')->name('detail');
	Route::get($cate_as . '<ename>$','article/cate')->name('cate');
	Route::get($cate_as . '<ename>/<type>$', 'article/cate')->name('cate_type');
	Route::rule($cate_as . '<ename>/<type>/<page>', 'article/cate')->name('cate_page');
	})->pattern([
		'ename' => '\w+',
		'type' => '\w+',
		'page'   => '\d+',
		'id'   => '\d+',
	]);
			
Route::rule('search/[:keywords]', 'index/search'); // 搜索
	