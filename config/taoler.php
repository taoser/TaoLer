<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-04-17 16:54:16
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \TaoLer\config\taoler.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */
// +----------------------------------------------------------------------
// | 网站公共配置
// +----------------------------------------------------------------------

return [
	// 应用名,此项不可更改
	'appname'	=> 'TaoLer',
    // 版本配置
    'version'   => '1.9.1',
	// 加盐
	'salt'		=> 'taoler',
	// 数据库备份目录
	'databasebackdir' => app()->getRootPath() .'data/',
	// 配置
	'config'	=>[
        'email_notice'	=> 0,
        'cate_show'	=> 1,
        'area_show'	=> 1,
		'regist_check'	=> 0,
		'posts_check'	=> 0,
		'commnets_check'	=> 0,
		'login_captcha'	=> 0,
		'post_captcha'	=> 0,
	],

	// 百度标签分词
	'baidu'	=> [
		'grant_type'	=> '',
		'client_id'		=> '',
		'client_secret'	=> '',
		'access_token'	=> '',
		'push_api'		=> '',
	],

	// sitemap
	'sitemap' => [
		'map_num'   => '1000',
		'map_time'	=> 'daily',
		'map_level'	=> '0.5',
		'write_id'   => 0,

	],

	
];