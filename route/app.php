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

Route::get('hello/:name', 'index/hello');

Route::get('/', 'index.index/index'); // 首页路由
Route::get('admin', 'admin.index/index'); // 管理路由

Route::group(function(){
    Route::get('index', 'index/index');
    Route::get('category/<ename>$', 'category/getArticleListByEname')->name('cate');
    Route::get('article/<ename>/<id>$', 'Article/detail')->name('detail');
})->prefix('index.')
->pattern([
    'ename' => '[\w|\-]+',
    'type' => '\w+',
    'page'   => '\d+',
    'id'   => '\d+',
]);
