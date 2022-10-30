<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-30 10:21:48
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \github\TaoLer\config\app.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否启用事件
    'with_event'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
	'app_map'	=> [
	],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'	=> [
        'www' => 'index',
        'adm' => 'admin',
        'api' => 'api'
	],
    // 禁止URL访问的应用列表（自动多应用模式有效）

    // 异常页面的模板文件
    'exception_tmpl' => app()->getThinkPath() . 'tpl/think_exception.tpl',
	
    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,
	//异常页面模板
	'http_exception_template' => [
		404 => \think\facade\App::getAppPath() . '404.html',
		500 => \think\facade\App::getAppPath() . '404.html',
    ],
];
