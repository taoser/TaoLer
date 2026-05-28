
<?php
use think\facade\Route;
// Route::rule('admin/:controller/:action','\app\admin\controller\:controller@:action');
// admin模块
Route::group('admin',function () {
    
    Route::get('/','index/index');
    Route::get('index','index/index');
    Route::rule('login','login/index');
	Route::get('system/getmenu','system.menu/getMenuJsonData')->name('get_menu');
    Route::get('system/getnav','system.menu/getnav')->name('get_nav');
    // Route::rule(':controller/:action$',':controller/:action');
    Route::rule(':name/:controller/:action$',':name.:controller/:action');
    Route::rule('addons/:name/:controller/:action$','addons.:name.:controller/:action');

    // 支持多级控制器（子目录控制器），如 system.admin 解析为 system/Admin
    // Route::rule(':controller/:action$', function($controller, $action) {
    //     $parts = explode('.', $controller);
    //     foreach($parts as &$part) {
    //         $part = ucfirst($part);
    //     }
    //     $controller = implode('/', $parts);
    //     // return $controller . '/' . $action;

    // });

   
})->middleware([
    \app\middleware\AdminAuth::class
])
->namespace('app\admin\controller');

// 执照图片
Route::rule('data/storage/[:id]/licence_pic/:name$', 'addons.jida.Lawyer/showImg')->name('lic_img');