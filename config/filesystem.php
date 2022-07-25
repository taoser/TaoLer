<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-24 09:57:31
 * @LastEditors: TaoLer
 * @Description: 文件存储优化版
 * @FilePath: \TaoLer\config\filesystem.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */

return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'local'),
    // 磁盘列表
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url'        => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        'sys'  => [
            'type' => 'local',
            'root' => app()->getRootPath() . 'public/sys',
            'url'        => '/sys',
        ],
        // 更多的磁盘配置信息
    ],
];
