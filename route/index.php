<?php

use think\facade\Route;

Route::group('',function(){
	Route::get('blog/index','user.Blog/index');

	// 定义首页路由
	Route::get('/', 'index/index');
	
	// 定义首页滑动页码路由
	Route::get('index/<page>$', 'index/index')->name('index_page');

	// 定义文章分类路由
    Route::get('<ename>-list$','article/cate')->name('cate');
    Route::get('<ename>-list/type-<type>$', 'article/cate')->name('cate_type');
    Route::get('<ename>-list/type-<type>/<page>$', 'article/cate')->name('cate_page');

	// 定义文章详情路由
	// $detail_as = config('taoler.url_rewrite.article_as') ?: '<ename>/'; //详情页URL别称
	Route::get('<ename>-list/<id>$', 'article/detail')->name('article_detail');
	Route::get('<ename>-list/<id>/<page>$', 'article/detail')->name('article_comment');
	

	// 定义文章添加路由
	Route::rule('article/add/<cate?>','article/add')->name('add_article');
	Route::rule('article/delete/<id>$','article/delete');
	Route::rule('article/tags','article/tags')->allowCrossDomain();
	Route::rule('article/edit/<id>$','article/edit')->name('article_edit');
	Route::get('article/catetree','article/getCateTree')->name('get_cate_tree');

	// comment
	Route::rule('comment/edit/[:id]','comment/edit');
	Route::rule('search/[:keywords]', 'index/search'); // 搜索

	// 登录注册
	Route::group(function () {
		Route::rule('user_login$','login/index')->name('user_login');
		Route::rule('user_forget$','login/forget')->name('user_forget');
		Route::rule('user_reg$','login/reg')->name('user_reg')->middleware(\app\middleware\CheckRegister::class);
		Route::rule('postcode$','login/postcode');
		Route::rule('sentemailcode$','login/sentMailCode');
		Route::rule('respass$','login/respass');
		Route::get('login_status', 'login/status')->name('login_status');
	});

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
	})->prefix('user/');

	Route::get('index/reply$','index/reply')->name('user_reply');
	Route::rule('search','Search/getSearch')->name('user_search');
	Route::get('message/nums$','message/nums')->name('user_message');
	
	//tag
	Route::group(function (){
		Route::get('tag$','getAllTag')->name('get_all_tag');
		Route::get('arttag$','getArticleTag')->name('get_art_tag');
		Route::get('tag/<ename>$', 'list')->name('tag_list');
	})->prefix('tag/');


	// 测试图片访问
	Route::get('fverify', 'staticfile/verify');
	Route::rule('storage/[:id]/licence_pic/:name$', '\\app\\index\\controller\\staticfile@showImg');

	// 动态路径路由会影响下面的路由，所以动态路由放下面

})
->pattern([
	'ename' => '[\w|\-]+',
	'id'   => '\w+',
	'type' => '\w+',
	'page'   => '\d+',
])
->namespace('app\index\controller');


// 开启多模块URL自动解析 `8.1+`版本开始支持
Route::auto();









	
	
	
	

	