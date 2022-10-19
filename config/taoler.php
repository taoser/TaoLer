<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-04 20:55:54
 * @LastEditors: TaoLer
 * @Description: 网站公共配置
 * @FilePath: \TaoLer\config\taoler.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
// +----------------------------------------------------------------------
// | 网站公共配置
// +----------------------------------------------------------------------

return [
	// 应用名,此项不可更改
	'appname'	=> 'TaoLer',
    // 系统版本
    'version'   => '2.0.6',
	// 模板版本
	'template_version' => '1.0.0',
	// 加盐
	'salt'		=> 'taoler',
	// 数据库备份目录
	'databasebackdir' => app()->getRootPath() .'data/',

	// 项目配置
	'config'	=>[
		// 注册开关
		'is_regist'   => 1,
		// 登录开关
		'is_login'   => 1,
		// 发帖开关
		'is_post'   => 1,
		// 评论开关
		'is_reply'   => 1,
		// 注册审核
		'regist_check'   => 0,
		// 发帖审核
		'posts_check'   => 0,
		// 评论审核
		'commnets_check'   => 0,
		// 注册验证类型 1验证码2邮箱3手机短信
		'regist_type'   => 1,
		// 登录验证码
		'login_captcha'   => 0,
		// 发帖验证码
		'post_captcha'   => 0,
		// 显示分类
        'cate_show'   => 1,
		// 显示用户归属地简称
        'area_show'   => 1,
		// 邮件通知
        'email_notice'   => 0,
		// 百度词条开关
		'baidu_title_switch'   => 0,
	],

	// 百度SEO标签分词
	'baidu'	=> [
		'grant_type'   => 'client_credentials',
		'client_id'   => '',
		'client_secret'   => '',
		'access_token'	=> '',
		'push_api'   => '',
	],

	// sitemap
	'sitemap' => [
		// 单文件记录数
		'map_num'   => '2000',
		'map_time'	=> 'daily',
		'map_level'	=> '0.5',
		// 已写id位标记
		'write_id'   => 0,

	],

	// URL美化
	'url_rewrite' => [
		// 详情url
		'article_as'   => '<ename>/',
		// 分类url
		'cate_as'   => '',
	],

	
];