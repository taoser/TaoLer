<?php

return [
	// 检测安装&登录
	\app\middleware\Index::class,
	// 浏览日志
	// \app\middleware\Browse::class,
	// 多语言加载
     \think\middleware\LoadLangPack::class,
    //接收消息
    \app\middleware\Message::class,

];