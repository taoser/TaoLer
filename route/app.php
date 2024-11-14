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

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

// Route::get('hello/:name', 'index/hello');

Route::get('/', 'index.index/index'); // 首页路由

// index
Route::group(function(){
    Route::get('index', 'index/index');
    Route::get('article/<ename>/<id>$', 'Article/detail')->name('detail');

    Route::get('category/<ename>$', 'category/getArticles')->name('cate');
    Route::get('category/<ename>/<page>$', 'category/getArticles')->name('cate_page');
    Route::get('category/<ename>/<type>$', 'category/getArticles')->name('cate_type');
	Route::get('category/<ename>/<type>/<page>$', 'category/getArticles')->name('cate_type_page');

    Route::get('user/<id>$', 'user/home')->name('user_home');
    Route::get('user/lgoin$', 'Login/index')->name('user_login');
    // Route::get('user/signin$', 'Login/signin')->name('user_signin');
    
})->prefix('index.')
->pattern([
    'ename' => '[\w|\-]+',
    'type' => '\w+',
    'page'   => '\d+',
    'id'   => '\d+',
]);
