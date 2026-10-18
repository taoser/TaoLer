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
    'autoload' => true,
    'hooks' => [],
    'route' => [],
    'service' => [],

    // 插件 HTTP 入口 URL 前缀（取 URL 首段匹配）
    // 命中时才启动插件系统；插件路由文件中的前缀会自动提取，此处为快速兜底。
    // 新增使用顶层路由的插件时，需在此补充其前缀。
    'entry_prefix' => [
        'app',    // 默认插件路由 app/:addon/:controller/:action
        'apps',   // sign 插件
        'pay',    // pay 插件
        'shop',   // pay 插件（支付回调/收银台）
        'taocai', // taocai 插件
        'addons', // huodong 等插件
    ],
];