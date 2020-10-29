<?php
// 事件定义文件
return [
    'bind'      => [
	'Message' => 'app\event\Message',
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
		'Message'  => ['app\listener\Message'],
		'CommMsg'  => ['app\listener\CommMsg'],
    ],

    'subscribe' => [
    ],
];
