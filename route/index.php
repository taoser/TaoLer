<?php

use think\facade\Route;
use think\Response;

// Route::get('static/:path', function (string $path) {
// 		$filename = public_path() . 'static/' . ltrim($path, '/');
// 		if (!is_file($filename)) {
// 			return response('404 Not Found!!', 404);
// 		}
// 		return new \think\worker\response\File($filename);
// 	})->pattern(['path' => '.*\.\w+$']);

Route::group('',function () {

	// 定义首页路由
	Route::get('/', 'index/index');

	// Route::get('/', function () {
	// 	return Response::create('hello world');
	// });
	
	// 定义首页滑动页码路由
	Route::get('index/<page>$', 'index/index')->name('index_page');

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
	

	// 定义文章分类路由
    Route::get('<ename>-list$','category/list')->name('cate');
	Route::get('<ename>-list/p-<page>$','category/list')->name('cate_page');
    Route::get('<ename>-list/flag-<flag>$', 'category/list')->name('cate_flag');
    Route::get('<ename>-list/flag-<flag>/p-<page>$', 'category/list')->name('cate_flag_page');

	// 定义文章详情路由
	Route::get('<ename>-list/<id>$', 'article/detail')->name('article_detail');
	Route::get('<ename>-list/<id>/p-<page>$', 'article/detail')->name('article_comment');
	

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
		Route::get('login$', 'login/index')->name('login_index');
		Route::post('gologin$', 'login/login')->name('user_login');
		Route::post('forget$', 'login/forget')->name('user_forget');
		Route::post('reg$', 'login/reg')->name('user_reg')->middleware(\app\middleware\CheckRegister::class);
		Route::post('postcode$', 'login/postcode');
		Route::post('sentemailcode$', 'login/sentMailCode');
		Route::post('respass$', 'login/respass');
		Route::get('login-status', 'login/status')->name('login_status');
	});

	// 用户中心
	Route::group('user',function () {
		Route::get('<id>$', 'user/home')->name('user_home')->pattern(['id'   => '\d+',]);
		Route::get('idx$', 'user/index')->name('user_index');
		Route::get('set$', 'user/set')->name('user_set');
		Route::get('message$', 'user/message');
		Route::get('post$', 'user/post');
		Route::get('article$', 'user/myArticles');
		Route::post('editpv$', 'user/editPv');
		Route::post('updatetime$', 'user/updateTime');
		Route::get('mycoll$', 'user/myCollect');
		Route::get('colldel$', 'user/collDel');
		Route::get('setpass$', 'user/setPass');
		Route::get('activate$', 'user/activate');
		Route::get('active$', 'user/active');
		Route::get('uploadHeadImg$', 'user/uploadHeadImg');
		Route::get('logout$', 'user/logout')->name('user_logout');
	});

	Route::get('index/reply$', 'index/reply')->name('user_reply');
	Route::rule('search', 'Search/getSearch')->name('user_search');
	Route::get('message/nums$', 'message/nums')->name('user_message');
	
	//tag
	Route::group(function (){
		Route::get('tag$', 'tag/getAllTag')->name('get_all_tag');
		Route::get('arttag$', 'tag/getArticleTag')->name('get_art_tag');
		Route::get('tag/<ename>$', 'tag/list')->name('tag_list');
	});

	// 测试图片访问
	Route::get('fverify', 'staticfile/verify');
	// Route::get('storage/[:id]/licence_pic/:name$', '\\app\\index\\controller\\staticfile@showImg');

	// 之后（字符串路由形式，能被正确解析）
	// Route::miss('index/miss');

	Route::miss(function() {
		return response('404 Not Found!', 404);
	});

})->namespace('app\index\controller')
->middleware([
	\app\middleware\Index::class,
	\app\middleware\Browse::class,
	\app\middleware\Message::class,
])->pattern([
	'ename' => '[\w|\-]+',
	'id'   => '\w+',
	'type' => '\w+',
	'page'   => '\d+',
]);


	