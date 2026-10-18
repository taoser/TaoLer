<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:26
 * @LastEditTime: 2026-10-18 14:28:19
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\vendor\taoser\think-addons\src\addons\Service.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
declare(strict_types=1);

namespace taoser\addons;

use think\Route;
use think\helper\Str;
use taoser\addons\middleware\Addons;

/**
 * 插件服务
 * Class Service
 * @package think\addons
 */
class Service extends \think\Service
{
    protected $addons_path;

    // 注册系统服务，将服务绑定到容器中
    public function register()
    {
        $this->app->bind('addons', AddonsSystem::class);  
    }

    public function boot()
    {
        $addonsSystem = $this->app->addons;

        $addonsSystem->autoload();

        $route = $this->app->route;
        $execute = '\\taoser\\addons\\Route::execute';

        $middlewareArr = [Addons::class];
        if (is_file($addonsSystem->getAddonsPath() . 'middleware.php')) {
            $middlewareArr = array_merge($middlewareArr, include $addonsSystem->getAddonsPath() . 'middleware.php');
        }

        $route->rule("app/:addon/[:controller]/[:action]", $execute)->middleware($middlewareArr);
        
    }

}