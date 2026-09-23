<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-23 22:44:31
 * @LastEditors: TaoLer
 * @Description: admin模块 后台路由
 * @Version: V4.0.0
 * @FilePath: \TaoLer\route\admin.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
use think\Request;
use think\facade\Route;
use think\facade\Config;

// Route::rule('admin/auth_rule/lists','system.AuthRule/list')->prefix('app\\admin\\controller\\');

// 执照图片
Route::rule('data/storage/[:id]/licence_pic/:name$', 'addons.jida.Lawyer/showImg')->name('lic_img');

$adminModuleName = '/' . trim(system_config('admin_module', 'admin'), '/');

Route::group($adminModuleName, function () {
    Route::get('/','index/index');
    Route::get('index','index/index')->name('admin-index');

    // 内容管理
    Route::group('content', function () {
        // 添加文章视图
        Route::view('article-add', 'content/article/add')->name('admin-add-article-page');
        // 编辑文章视图
        Route::get('article-edit', function (Request $request) {
            $id = $request->get('id/d');
            $article = \app\facade\Article::getInfo($id);
            return view('content/article/edit', ['article'=>$article]);
        })->name('admin-edit-article-page');
        // 标签视图
        Route::get('tag', function () {
            return view('content/tag/index');
        })->name('admin-tag-index-page');
        // 添加标签视图
        Route::get('tag-add', function () {
            return view('content/tag/add');
        })->name('admin-tag-add-page');
        // 编辑标签视图
        Route::get('tag-edit', function () {
            return view('content/tag/edit');
        })->name('admin-edit-tag-page');
    });

    // 登录接口
    Route::rule('login$','login/index')->name('admin-login');
    // 注册接口
    Route::rule('register$','login/register')->name('admin-register');
    // 注出接口
    Route::rule('logout$','system.admin/logout')->name('admin-logout');
    // 修改密码接口
    Route::rule('system/repass$','system.admin/repass')->name('admin-repass');
    // 基本资料接口
    Route::rule('system/info$','system.admin/info')->name('admin-info');
    // 基本资料设置接口
    Route::get('system/infoedit$','system.admin/infoEdit')->name('admin-infoEdit');
    // 清理缓存接口
    Route::post('system/clearcache$','system.admin/clearCache')->name('clear_cache');
    // 获取菜单接口
	Route::get('system/getmenu$','system.menu/getMenu')->name('get_menu');
    // 获取导航接口
    Route::get('system/getnav$','system.menu/getnav')->name('get_nav');

    Route::get('addons', 'Addons/index');
    Route::get('addons_config/:name', 'Addons/config');
    Route::post('addons_save', 'Addons/saveConfig');
    Route::post('addons_upload', 'Addons/upload');


   
    Route::get('system_config/get','system.Config/getConfig');
    Route::get('system_config/all','system.Config/getAll');

    // 可变路由
    Route::rule(':controller/:action$',':controller/:action');
    Route::rule(':dirname/:controller/:action$',':dirname.:controller/:action');
    
})->middleware([
    \app\middleware\AdminAuth::class
])
->namespace('app\admin\controller');