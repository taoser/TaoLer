<?php
use think\facade\Route;

Route::get('admin', 'admin.index/index'); // 管理路由

// admin
Route::group('admin', function(){
    // Route::get('captcha/[:config]$','\\think\\captcha\\CaptchaController@index')->name('admin_captcha');
    Route::get('index', 'index/index')->name('admin_index'); // 管理路由

    Route::get('getnav$', 'system.menu/getnav')->name('admin_nav');
    Route::get('console1$', 'index/console1')->name('console1');
    Route::get('console2$', 'index/console2')->name('console2');
    Route::get('info$', 'system.admin/info')->name('admin_info');
    Route::get('login$', 'login/index')->name('admin_login');
    Route::get('logout$', 'system.admin/logout')->name('admin_logout');

    Route::get('news$', 'index/news')->name('admin_news');
    Route::get('feedback$', 'Index/feedback')->name('admin_feedback');
    Route::get('weekarticle$', 'Index/weekForums')->name('admin_weekarticle');
    Route::get('weekcomment$', 'Index/weekComments')->name('admin_weekcomment');
    
    // Route::miss('index/index');
})
->prefix('admin.');