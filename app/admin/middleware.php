<?php

return [
	// 检测安装
	\app\middleware\Install::class,
	// 权限检测
	app\middleware\Auth::class,
];