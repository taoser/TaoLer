<?php

use think\facade\Route;

Route::group('',function(){
	// Route::get('user/blog','user\Blog/index');
	// Route::get('user/blog','user.Blog/index');


	// 上传路由
	Route::get('upload/index','upload/index');
	Route::post('upload/chunk','upload/chunk');
	Route::post('upload/merge','upload/merge');
	Route::post('upload/getUploadedChunk','upload/getUploadedChunk');
	Route::post('upload/cancelUpload','upload/cancelUpload');
	
	// 定义首页路由
	Route::get('user.blog/index','user.blog/index');
	Route::get('user/blog','user.blog/index');
	// 定义首页路由
	Route::get('/', 'index/index');
	
	// 定义首页滑动页码路由
	Route::get('index/<page>$', 'index/index')->name('index_page');

	// 定义文章分类路由
    Route::get('<ename>-list$','article/list')->name('cate');
    Route::get('<ename>-list/type-<type>$', 'article/list')->name('cate_type');
    Route::get('<ename>-list/type-<type>/<page>$', 'article/list')->name('cate_page');
//
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
		Route::post('login$','login/index')->name('user_login');
		Route::post('forget$','login/forget')->name('user_forget');
		Route::post('reg$','login/reg')->name('user_reg')->middleware(\app\middleware\CheckRegister::class);
		Route::post('postcode$','login/postcode');
		Route::post('sentemailcode$','login/sentMailCode');
		Route::post('respass$','login/respass');
		Route::get('login-status', 'login/status')->name('login_status');
	});

	// 用户中心
	Route::group('user',function () {
		Route::get('<id>$', 'user/home')->name('user_home'); 
		Route::get('index$', 'user/index')->name('user_index');
		Route::get('set$', 'user/set');
		Route::get('message$', 'user/message');
		Route::get('post$', 'user/post');
		Route::get('article$','user/myArticles');
		Route::post('editpv$','user/editPv');
		Route::post('updatetime$','user/updateTime');
		Route::get('mycoll$','user/myCollect');
		Route::get('colldel$','user/collDel');
		Route::get('setpass$','user/setPass');
		Route::get('activate$','user/activate');
		Route::get('active$','user/active');
		Route::get('uploadHeadImg$','user/uploadHeadImg');
		Route::get('logout$', 'user/logout');
	});

	Route::get('index/reply$','index/reply')->name('user_reply');
	Route::rule('search','Search/getSearch')->name('user_search');
	Route::get('message/nums$','message/nums')->name('user_message');
	
	//tag
	Route::group(function (){
		Route::get('tag$','tag/getAllTag')->name('get_all_tag');
		Route::get('arttag$','tag/getArticleTag')->name('get_art_tag');
		Route::get('tag/<ename>$', 'tag/list')->name('tag_list');
	});


	// 测试图片访问
	Route::get('fverify', 'staticfile/verify');
	Route::get('storage/[:id]/licence_pic/:name$', '\\app\\index\\controller\\staticfile@showImg');

	// 动态路径路由会影响下面的路由，所以动态路由放下面

	// Route::miss(function() {
	// 	return '404 Not Found!';
	// });

})
->pattern([
	'ename' => '[\w|\-]+',
	'id'   => '\w+',
	'type' => '\w+',
	'page'   => '\d+',
])->middleware([
	\app\middleware\Index::class,
	\app\middleware\Browse::class,
	\app\middleware\Message::class,
])
->namespace('app\index\controller');

// 开启多模块URL自动解析 `8.1+`版本开始支持
// Route::auto();









	
	
	
	

	