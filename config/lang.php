<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-14 20:47:36
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\config\lang.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
// +----------------------------------------------------------------------
// | 多语言设置
// +----------------------------------------------------------------------

return [
    // 默认语言
    'default_lang'        => env('DEFAULT_LANG', 'zh-cn'),
    // 自动侦测浏览器语言
    'auto_detect_browser' => false,
    // 允许的语言列表
    'allow_lang_list'     => ['zh-cn','zh-tw','en-us','ja-jp','ko-kr','es-es','fr-fr','de-de','ru-ru'],
    // 多语言自动侦测变量名
    'detect_var'          => 'lang',
    // 是否使用Cookie记录
    'use_cookie'          => true,
    // 多语言cookie变量
    'cookie_var'          => 'think_lang',
    // 多语言header变量
    'header_var'          => 'think-lang',
    // 扩展语言包
    'extend_list'         => [],
    // Accept-Language转义为对应语言包名称
    'accept_language'     => [
        'zh-hans-cn' => 'zh-cn',
    ],
    // 是否支持语言分组
    'allow_group'         => true,
];
