<?php
/**
 * @Program: table.css 2023/5/21
 * @FilePath: ${NAMESPACE}\works.php
 * @Description: works.php
 * @LastEditTime: 2023-05-21 07:30:39
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

use think\facade\Route;

Route::get('works/add$', 'works/add');
Route::get('works/picwall/<type>$', 'works/picwall');
Route::get('works/detail/<id>$', 'works/detail');
Route::get('works/getData', 'works/getData');
Route::rule('works/saveBase', 'works/saveBase');
Route::post('works/comment', 'works/comment');

//Route::rule('login$','loginNew/index')->name('user_login');