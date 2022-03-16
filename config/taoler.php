<?php
// +----------------------------------------------------------------------
// | 网站公共配置
// +----------------------------------------------------------------------

return [
	//应用名,此项不可更改
	'appname'	=> 'TaoLer',
    //版本配置
    'version'   => '1.9.0',
	//加盐
	'salt'		=> 'taoler',
	//数据库备份目录
	'databasebackdir' => app()->getRootPath() .'data/',
	//配置
	'config'	=>[
        'email_notice'	=> 0,
        'cate_show'	=> 0,
        'area_show'	=> 0,
		'regist_check'	=> 0,
		'posts_check'	=> 0,
		'commnets_check'	=> 0,
		'login_captcha'	=> 0,
		'post_captcha'	=> 0,

	]
	
];