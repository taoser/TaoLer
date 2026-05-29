<?php
use think\facade\Route;

Route::group('admin',function () {
    Route::get('/','index/index');
    Route::get('index','index/index');
    Route::rule('login','login/index');
	Route::get('system/getmenu','system.menu/getMenuJsonData')->name('get_menu');
    Route::get('system/getnav','system.menu/getnav')->name('get_nav');
    // Route::rule(':controller/:action$',':controller/:action');
    Route::rule(':name/:controller/:action$',':name.:controller/:action');
    // Route::rule('addons/:name/:controller/:action$','addons.:name.:controller/:action');
    
})->middleware([
    \app\middleware\AdminAuth::class
])->namespace('app\admin\controller');