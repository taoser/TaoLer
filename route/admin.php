<?php
use think\facade\Route;

// Route::rule('admin/auth_rule/lists','system.AuthRule/list')->prefix('app\\admin\\controller\\');

Route::group('admin',function () {
    Route::get('/','index/index');
    Route::get('index','index/index');
    Route::rule('login','login/index');
	Route::get('system/getmenu','system.menu/getMenuJsonData')->name('get_menu');
    Route::get('system/getnav','system.menu/getnav')->name('get_nav');
    // Route::get('auth_rule/list','system.AuthRule/list');

    Route::rule(':controller/:action$',':controller/:action');
    Route::rule(':dirname/:controller/:action$',':dirname.:controller/:action');
    
})->middleware([
    \app\middleware\AdminAuth::class
])->namespace('app\admin\controller');

// Route::auto();