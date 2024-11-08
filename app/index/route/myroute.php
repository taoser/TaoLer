<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-06-29 10:57:40
 * @LastEditors: TaoLer
 * @Description: 前端路由设置
 * @FilePath: \TaoLer\app\index\route\route.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */

//路由文件匹配有先后顺序，在前面的文件匹配到即停止
use think\facade\Route;

//私有路由
Route::get('user/key', 'api/key');
Route::get('user/keylist', 'api/keyList');
Route::get('user/pay', 'api/pay');
//Route::get('user/ispay', 'api/isPay')->name('user_auth_ispay');
Route::get('user/order', 'shop/myorder');
Route::get('user/myorder', 'shop/getMyorder');
Route::get('doc/timeline','doc/timeline');
Route::get('shop/pay', 'shop/pay');
