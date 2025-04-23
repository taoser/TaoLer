<?php
// 事件定义文件
return [
    'bind'  => [
        'UserLogin'     => 'app\event\UserLogin',
	    'Message'       => 'app\event\Message',
        'ArticlePush'   => 'app\event\ArticlePush'
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'UserLogin' => ['app\listener\UserLogin'],
		'Message'  => ['app\listener\Message'],
		'CommMsg'  => ['app\listener\CommMsg'],
    ],

    'subscribe' => [
        'article'   => 'app\subscribe\Article',
    ],
];
