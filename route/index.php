<?php

use think\facade\Route;
use think\Response;

Route::group('',function () {

	// 首页
	Route::get('/', 'index/index');
	// 滑动页码
	Route::get('index/<page>$', 'index/index')->name('index_page');

	Route::group('<ename>-list', function () {
		// 类别
		Route::get('', 'category/list')->name('category');
		Route::get('p-<page>$','category/list')->name('category_page');
		Route::get('flag-<flag>$', 'category/list')->name('category_flag');
		Route::get('flag-<flag>/p-<page>$', 'category/list')->name('category_flag_page');
		// 详情
		Route::get('<id>$', 'article/detail')->name('article_detail');
		Route::get('<id>/p-<page>$', 'article/detail')->name('article_comment');
	});
	
	// 文章
	Route::group('article',function () {
		Route::rule('add/<cate?>', 'article/add')->name('add_article');
		Route::get('edit/<id>$', 'article/edit')->name('article_edit');
		Route::post('edit-data/<id>$', 'article/editData')->name('article_edit_data');
		Route::rule('delete/<id>$', 'article/delete');
		Route::rule('tags', 'article/tags')->allowCrossDomain();
		Route::get('catetree', 'category/getArticleSelectCategoryTree')->name('get_cate_tree');
	});

	// 用户中心
	Route::group('user',function () {
		// 用户中心首页
		Route::get('/', function() {
			return view('user/index');
		})->name('user_page');


		Route::get('<id>$', 'user/home')->name('user_home')->pattern(['id'   => '\d+',]);
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

	// 注册/登录/找回密码/发送验证码
	Route::group(function () {
		// 登录注册页面
		Route::get('login$', function() {
			return view('auth/login');
		})->name('login_page');
		// 注册页面
		Route::get('register$', function() {
			return view('auth/register');
		})->name('register_page');
		// 找回密码页面
		Route::get('forget$', function() {
			return view('auth/forget');
		})->name('forget_page');
		
		Route::post('login$', 'auth/login')->name('user_login');
		Route::post('register$', 'auth/register')->name('user_register_post');

		Route::post('forget$', 'auth/forget')->name('user_forget');
		
		Route::post('postcode$', 'auth/postcode');
		Route::post('sentemailcode$', 'auth/sentMailCode');
		Route::post('respass$', 'auth/respass');
		Route::get('login-status', 'auth/status')->name('login_status');
	});

	Route::post('language$', 'index/language')->name('language');

	// tag
	Route::group(function (){
		Route::get('tag$', 'tag/getAllTag')->name('get_all_tag');
		Route::get('arttag$', 'tag/getArticleTag')->name('get_art_tag');
		Route::get('tag/<ename>$', 'tag/list')->name('tag_list');
	});

	// comment
	Route::get('index/reply$', 'index/reply')->name('user_reply');
	Route::rule('comment/edit/[:id]','comment/edit');
	Route::rule('search/[:keywords]', 'search/getSearch')->name('user_search'); // 搜索
	Route::get('message/nums$', 'message/nums')->name('user_message');
	

	// 上传
	Route::get('upload/index','upload/index');
	Route::post('upload/chunk','upload/chunk');
	Route::post('upload/merge','upload/merge');
	Route::post('upload/getUploadedChunk','upload/getUploadedChunk');
	Route::post('upload/cancelUpload','upload/cancelUpload');

	// 测试图片访问
	Route::get('fverify', 'staticfile/verify');
	// Route::get('storage/[:id]/licence_pic/:name$', '\\app\\index\\controller\\staticfile@showImg');



	// Route::get('/sse/time', function () {
	// 	$generator = function () {
	// 		while (true) {
	// 			yield 'data: ' . json_encode([
	// 				'time' => date('Y-m-d H:i:s'),
	// 				'memory' => memory_get_usage()
	// 			]) . "\n\n";
				
	// 			sleep(1); // 每秒推送一次
	// 		}
	// 	};

	// 	return (new \think\worker\response\Iterator($generator()))
	// 		->header([
	// 			'Content-Type' => 'text/event-stream',
	// 			'Cache-Control' => 'no-cache',
	// 	]);

	// });

	// 之后（字符串路由形式，能被正确解析）
	// Route::miss('index/miss');

	Route::miss(function() {
		return response('404 Not Found!', 404);
	});

})
->namespace('app\index\controller')
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


	