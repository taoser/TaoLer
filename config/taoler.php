<?php
/*
 * @Author: TaoLer <317927823@qq.com>
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
    'version'   => '2.3.9',
	// 模板版本
	'template_version' => '1.0.0',
	// 加盐
	'salt'		=> 'taoler',
	// 数据库备份目录
	'databasebackdir' => app()->getRootPath() .'data/',

	// 项目配置
	'config'	=>[
		// 注册开关
		'is_regist'   => 0,
		// 登录开关
		'is_login'   => 1,
		// 发帖开关
		'is_post'   => 1,
		// 评论开关
		'is_reply'   => 1,
		// 注册审核
		'regist_check'   => 1,
		// 发帖审核
		'posts_check'   => 1,
		// 评论审核
		'commnets_check'   => 1,
		// 注册验证类型 1验证码2邮箱3手机短信
		'regist_type'   => 0,
		// 登录验证码
		'login_captcha'   => 1,
		// 发帖验证码
		'post_captcha'   => 0,
		// 显示分类
        'cate_show'   => 0,
		// 显示用户归属地简称
        'area_show'   => 0,
		// 邮件通知
        'email_notice'   => 0,
        // 菜单位置
        'nav_top'   => 1,
        // 置顶文章显示方式1列表0滑动
        'top_show'   => 1,

	],

	// URL美化
	'url_rewrite' => [
		// 详情url
		'article_as'   => '<ename>/',
		// 分类url
		'cate_as'   => '',
	],

	
];