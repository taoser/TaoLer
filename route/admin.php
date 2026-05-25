
<?php
use think\facade\Route;
// admin模块
Route::group('admin',function () {
    Route::get('index','index/index');
	Route::get('system/getmenu','system.menu/getMenuJsonData')->name('get_menu');
})
->namespace('app\admin\controller');