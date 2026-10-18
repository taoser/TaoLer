<?php
// +----------------------------------------------------------------------
// | thinkphp5 Addons [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.zzstudio.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Byron Sampson <xiaobo.sun@qq.com>
// +----------------------------------------------------------------------

return [
    // 是否自动读取取插件钩子配置信息（默认是开启）
    'autoload' => true,
    // 当关闭自动获取配置时需要手动配置hooks信息
    'hooks' => [
         // 可以定义多个钩子
        // 'testhook'=>'test' // 键为钩子名称，用于在业务中自定义钩子处理，值为实现该钩子的插件，
					// 多个插件可以用数组也可以用逗号分割
    ],
    'route' => [
//       '/taocai/savedata' => 'taocai/Api/saveData', // 键为请求中的路由地址，值为插件/控制器/方法
    ],
    'service' => [],

    // 延迟加载配置
    'lazy_load' => [
        'enabled' => true,              // 启用延迟加载
        'preload_hooks' => [            // 预加载的钩子
            'AddonsInit',
            'app_init',
        ],
        'high_frequency_threshold' => 10, // 高频阈值
    ],

    // 插件 HTTP 入口 URL 前缀（取 URL 首段匹配）
    // 仅命中前缀的请求才会启动插件系统；其他请求（前台控制器、admin、api）零插件加载。
    // 插件路由文件中的顶层前缀会自动提取合并，此处为快速兜底配置。
    'entry_prefix' => [
        'app',    // 默认插件路由 app/:addon/:controller/:action
        'apps',   // sign 插件
        'pay',    // pay 插件
        'shop',   // pay 插件（支付回调/收银台）
        'taocai', // taocai 插件
        'addons', // huodong 等插件
    ],
];