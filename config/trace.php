<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-03 04:55:42
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \TaoLer\config\trace.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
// +----------------------------------------------------------------------
// | Trace设置 开启调试模式后有效
// +----------------------------------------------------------------------
return [
    'type' => 'console',
    'tabs' => [
        'base'                 => '基本',
        'file'                 => '文件',
        'error|notice|warning' => '错误',
        'sql'                  => 'SQL',
        'debug|info'           => '调试',
    ],
];
