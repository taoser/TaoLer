<?php
// 事件定义文件
return [
    'bind'      => [
        'UserLogin' => 'app\event\UserLogin',
	    'Message' => 'app\event\Message',
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
    ],
];
