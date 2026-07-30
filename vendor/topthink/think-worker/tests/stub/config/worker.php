<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\worker\websocket\Handler;

return [
    'http'       => [
        'enable'     => env('HTTP_ENABLE', true),
        'host'       => '0.0.0.0',
        'port'       => 8080,
        'worker_num' => 2,
        'options'    => [],
    ],
    'websocket'  => [
        'enable'        => env('WEBSOCKET_ENABLE', true),
        'handler'       => Handler::class,
        'ping_interval' => 25000,
        'ping_timeout'  => 60000,
    ],
    //队列
    'queue'      => [
        'enable'  => env('QUEUE_ENABLE', true),
        'workers' => [
            'default' => [],
        ],
    ],
    //共享数据
    'conduit'    => [
        'type' => 'socket',
    ],
    'hot_update' => [
        'enable'  => env('HOT_ENABLE', true),
        'name'    => ['*.php'],
        'include' => [app_path(), config_path(), root_path('route')],
        'exclude' => [],
    ],
];
