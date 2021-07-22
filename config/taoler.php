<?php
// +----------------------------------------------------------------------
// | 网站公共配置
// +----------------------------------------------------------------------

return [
	//应用名,此项不可更改
	'appname'	=> 'TaoLer',
    //版本配置
    'version'   => '1.7.23',
	//加盐
	'salt'		=> 'taoler',
	//数据库备份目录
	'databasebackdir' => app()->getRootPath() .'data/',
	//配置
	'config'	=>[
		'area_show'	=> 0,
		'email_notice'	=> 1,
	]
	
	
];