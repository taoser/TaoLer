<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-04-27 14:22:42
 * @LastEditors: TaoLer
 * @Description: 网站公共配置
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
    'version'   => '1.9.6',
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
		'client_id'   => 'aa',
		'client_secret'   => 'aa',
		'access_token'	=> '',
		'push_api'   => 'http://',
	],

	// sitemap
	'sitemap' => [
		// 每次生成数量
		'map_num'   => '1000',
		'map_time'	=> 'daily',
		'map_level'	=> '0.5',
		// 已生成id位标记
		'write_id'   => 12,

	],

	// URL美化
	'url_rewrite' => [
		// 详情url
		'article_as'   => 'article/',
		// 分类url
		'cate_as'   => 'column/',
	],

	
];