<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-13 20:31:36
 * @LastEditTime: 2026-09-13 20:48:05
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\middleware\SwitchLang.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\middleware;

use think\facade\Lang;

class SwitchLang
{
    public function handle($request, \Closure $next)
    {
        $currentLang = system_config('current_lang', 'en-us');
        $currentLang = strtolower($currentLang);

        // 切换语言
        Lang::switchLangSet($currentLang);
        
        return $next($request);
    }
}
