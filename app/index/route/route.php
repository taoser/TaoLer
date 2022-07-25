<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-25 10:51:53
 * @LastEditors: TaoLer
 * @Description: 前端路由设置
 * @FilePath: \TaoLer\app\index\route\route.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
use think\facade\Route;

//详情页URL别称
$detail_as = config('taoler.url_rewrite.article_as');
//分类别称
$cate_as = config('taoler.url_rewrite.cate_as');

Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');
Route::rule('/', 'index'); // 首页访问路由

Route::get('index/reply','index/reply')->name('user_reply');
Route::rule('search','Search/getSearch')->name('user_search');
Route::get('message/nums','message/nums')->name('user_message');
Route::get('tag/:tag', 'Tag/list')->name('tag_list');
// 用户中心
Route::group(function () {
	Route::get('u/:id$', 'user/home')->name('user_home'); 
	Route::get('user/index', 'user/index');
	Route::get('user/set', 'user/set');
	Route::get('user/message', 'user/message');
	Route::get('user/post', 'user/post');
	Route::get('user/article','user/artList');
	Route::get('user/coll','user/collList');
	Route::get('user/colldel','user/collDel');
	Route::get('user/setpass','user/setPass');
	Route::get('user/activate','user/activate');
	Route::get('user/active','user/active');
	Route::get('user/uploadHeadImg','user/uploadHeadImg');
	Route::get('logout', 'user/logout');
});

// 登录注册
Route::group(function () {
	Route::rule('login','login/index');
	Route::rule('forget','login/forget');
	Route::rule('postcode','login/postcode');
	Route::rule('sentemailcode','login/sentMailCode');
	Route::rule('respass','login/respass');
	Route::rule('reg$','Login/reg')
		->middleware(\app\middleware\CheckRegister::class);
});

	

// article
Route::group(function () use($detail_as,$cate_as){
	Route::rule('add/[:cate]','Article/add');
	Route::rule('delete/[:id]','Article/delete');
	Route::rule('tags','Article/tags')->allowCrossDomain();
	Route::rule('edit/[:id]','Article/edit');
	// 动态路径路由会影响下面的路由，所以动态路由放下面
	Route::get($detail_as . ':id$', 'article/detail')->name('article_detail');
	Route::get($cate_as . '<ename>$','article/cate')->name('cate');
	Route::get($cate_as . '<ename>/<type>$', 'article/cate')->name('cate_type');
	Route::get($cate_as . '<ename>/<type>/<page>', 'article/cate')->name('cate_page');
	})->pattern([
		'ename' => '\w+',
		'type' => '\w+',
		'page'   => '\d+',
		'id'   => '\d+',
	]);
			
Route::rule('search/[:keywords]', 'index/search'); // 搜索
	