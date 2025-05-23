<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-08-15 12:24:56
 * @LastEditors: TaoLer
 * @Description: 前端路由设置
 * @FilePath: \TaoLer\app\index\route\route.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
use think\facade\Route;

Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');

Route::get('/', 'index'); // 首页访问路由

// 定义主域名路由
// Route::group('index',function(){
// })->namespace('app\index\controller');

	Route::get('index/<page>$', 'index')->name('index_page')->pattern([
		'page'   => '\d+',
	]); // 首页页码路由
	
	Route::get('index/reply$','index/reply')->name('user_reply');
	Route::rule('search','Search/getSearch')->name('user_search');
	Route::get('message/nums$','message/nums')->name('user_message');
	
	// 用户中心
	Route::group('user',function () {
		Route::get('<id>$', 'home')->name('user_home'); 
		Route::get('index$', 'index')->name('user_index');
		Route::get('set$', 'set');
		Route::get('message$', 'message');
		Route::get('post$', 'post');
		Route::get('article$','myArticles');
		Route::post('editpv$','editPv');
		Route::post('updatetime$','updateTime');
		Route::get('mycoll$','myCollect');
		Route::get('colldel$','collDel');
		Route::get('setpass$','setPass');
		Route::get('activate$','activate');
		Route::get('active$','active');
		Route::get('uploadHeadImg$','uploadHeadImg');
		Route::get('logout$', 'logout');
	})->prefix('user/')->pattern([
			'id'   => '\d+',
		]);
	
	// 登录注册
	Route::group(function () {
		Route::rule('user_login$','index')->name('user_login');
		Route::rule('user_forget$','forget')->name('user_forget');
		Route::rule('user_reg$','reg')->name('user_reg')->middleware(\app\middleware\CheckRegister::class);
		Route::rule('postcode$','postcode');
		Route::rule('sentemailcode$','sentMailCode');
		Route::rule('respass$','respass');
		Route::get('login_status', 'status')->name('login_status');
	})->prefix('login/');
	
	// comment
	Route::rule('comment/edit/[:id]','comment/edit');
	
	//tag
	Route::group(function (){
		Route::get('tag$','getAllTag')->name('get_all_tag');
		Route::get('arttag$','getArticleTag')->name('get_art_tag');
		Route::get('tag/<ename>$', 'list')->name('tag_list');
	})->prefix('tag/');
	
	
	Route::rule('search/[:keywords]', 'index/search'); // 搜索
	
	// article分类和详情路由 ！放到最后！
	Route::group(function (){
	
		Route::rule('article/add/<cate?>','add')->name('add_article');
		Route::rule('article/delete/<id>$','delete');
		Route::rule('article/tags','tags')->allowCrossDomain();
		Route::rule('article/edit/<id>$','edit')->name('article_edit');
		Route::get('article/catetree','getCateTree')->name('get_cate_tree');
	
		// 动态路径路由会影响下面的路由，所以动态路由放下面
		// 详情
		$detail_as = config('taoler.url_rewrite.article_as') ?: '<ename>/'; //详情页URL别称
		Route::get($detail_as.'<id>$', 'detail')->name('article_detail');
		Route::get($detail_as.'<id>/<page>$', 'detail')->name('article_comment');
		
		// 分类
		$cate_as = config('taoler.url_rewrite.cate_as'); //分类别称
		// var_dump($cate_as);
		Route::get('<ename>$','cate')->name('cate');
		Route::get($cate_as.'<ename>/type-<type>$', 'cate')->name('cate_type');
		Route::get($cate_as.'<ename>/type-<type>/<page>$', 'cate')->name('cate_page');
	
	})->prefix('article/')->pattern([
			'ename' => '[\w|\-]+',
			'id'   => '\w+',
			'type' => '\w+',
			'page'   => '\d+',
		]);
   





			

	