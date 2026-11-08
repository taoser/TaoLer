<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-11-06 10:23:14
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\config\session.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

return [
    // session name
    'name'           => 'PHPSESSID',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持file cache
    'type'           => 'file',
    // 存储连接标识 当type使用cache的时候有效
    'store'          => null,
    // 过期时间
    'expire'         => 1800,
    // 前缀
    'prefix'         => '',

    // --------file驱动--------
    // 路径
    //'path'           => '',
    // 数据压缩
    //'data_compress'  => false,
    // 垃圾回收概率
    //'gc_probability' => 1,
    // 垃圾回收除数
    //'gc_divisor'     => 100,
];
