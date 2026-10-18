<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-07-30 07:19:59
 * @LastEditTime: 2026-10-18 20:20:51
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\config\cache.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    // 默认缓存驱动
    'default' => env('cache.driver', 'file'),

    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => runtime_path() . 'cache',
            // 缓存前缀
            'prefix'     => '',
            // 缓存有效期 0表示永久缓存
            'expire'     => 600,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        // redis缓存
        'redis'   =>  [
            // 驱动方式
            'type'      => 'redis',
            // 服务器地址
            'host'      => '127.0.0.1',
            // 缓存前缀
            'prefix'    => 'tao:',
            // 缓存有效期 0表示永久缓存
            'expire'     => 3600
        ],
    ],
];