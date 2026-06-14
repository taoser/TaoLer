<?php
use think\facade\Route;
use think\facade\Config;

// Route::rule('admin/auth_rule/lists','system.AuthRule/list')->prefix('app\\admin\\controller\\');

// 执照图片
Route::rule('data/storage/[:id]/licence_pic/:name$', 'addons.jida.Lawyer/showImg')->name('lic_img');

$moduleName = Config::get('taoler.module_name');

Route::group($moduleName, function () {

    Route::get('/','index/index');
    Route::get('index','index/index');
    // 登录接口
    Route::post('login$','login/index')->name('login');
    // 注出接口
    Route::post('logout$','system.admin/logout')->name('logout');
    // 修改密码接口
    Route::post('system/repass$','system.admin/repass')->name('repass');
    // 清理缓存接口
    Route::post('system/clearcache$','system.admin/clearCache')->name('clear_cache');
    // 获取菜单接口
	Route::get('system/getmenu$','system.menu/getMenuJsonData')->name('get_menu');
    // 获取导航接口
    Route::get('system/getnav$','system.menu/getnav')->name('get_nav');

    Route::rule(':controller/:action$',':controller/:action');
    Route::rule(':dirname/:controller/:action$',':dirname.:controller/:action');
    
})->middleware([
    \app\middleware\AdminAuth::class
])
->namespace('app\admin\controller');

// Route::auto();