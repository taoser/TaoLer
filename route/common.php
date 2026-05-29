<?php


use think\facade\Route;

Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');

// 查看已注册的路由
