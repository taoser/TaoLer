<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-14 10:18:56
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\middleware.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
// 全局中间件定义文件
return [
    // 节流
    \think\middleware\Throttle::class,
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    \think\middleware\LoadLangPack::class,
    // Session初始化
    \think\middleware\SessionInit::class,
     // 静态文件
    //  \app\middleware\StaticFile::class,
];
