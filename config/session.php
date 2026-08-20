<?php
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
    'expire'         => 1440,
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
