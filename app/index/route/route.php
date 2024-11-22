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

//详情页URL别称
$detail_as = config('taoler.url_rewrite.article_as');
//分类别称
$cate_as = config('taoler.url_rewrite.cate_as');

Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');
Route::rule('/', 'index'); // 首页访问路由

Route::get('index/reply$','index/reply')->name('user_reply');
Route::rule('search','Search/getSearch')->name('user_search');
Route::get('message/nums$','message/nums')->name('user_message');
Route::get('tag/:ename', 'Tag/list')->name('tag_list');

// 用户中心
Route::group('user',function () {
	Route::get(':id$', 'home')->name('user_home'); 
	Route::get('index$', 'index')->name('user_index');
	Route::get('set$', 'set');
	Route::get('message$', 'message');
	Route::get('post$', 'post');
	Route::get('article$','artList');
	Route::post('editpv$','editPv');
	Route::post('updatetime$','updateTime');
	Route::get('coll$','collList');
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

// article
Route::group('art',function (){
	Route::rule('add/[:cate]','add')->name('add_article');
	Route::rule('delete/[:id]','delete');
	Route::rule('tags','tags')->allowCrossDomain();
	Route::rule('edit/[:id]','edit');
	Route::get('article/catetree','getCateTree');
	Route::get('download/[:id]','download');

})->prefix('article/');

//tag
Route::get('tag','tag/getAllTag')->name('get_all_tag');
Route::get('arttag','tag/getArticleTag')->name('get_art_tag');

Route::rule('search/[:keywords]', 'index/search'); // 搜索

// article分类和详情路由 ！放到最后！
Route::group(function () use($detail_as, $cate_as){
	// 动态路径路由会影响下面的路由，所以动态路由放下面
	Route::get($detail_as . ':id$', 'article/detail')->name('article_detail');
    Route::get($detail_as . '<id>/<page>$', 'article/detail')->name('article_comment');
    //分类
	Route::get($cate_as . '<ename>$','article/cate')->name('cate')->cache(180);
	Route::get($cate_as . '<ename>/<type>$', 'article/cate')->name('cate_type');
	// 分页路由
	Route::get($cate_as . '<ename>/<type>/<page>$', 'article/cate')->name('cate_page');
})->pattern([
		'ename' => '[\w|\-]+',
		'type' => '\w+',
		'page'   => '\d+',
		'id'   => '\d+',
	]);
			

	