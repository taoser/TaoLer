<?php

use think\facade\Route;
use app\middleware\AccessControl;

Route::group('addons',function (){
    Route::rule(':addon/:controller/:action',':controller/:action');
})->middleware(AccessControl::class)
->namespace('addons\:addon\controller');