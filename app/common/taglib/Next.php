<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-16 16:43:06
 * @LastEditTime: 2026-09-16 16:45:20
 * @LastEditors: TaoLer
 * @Description: 下一篇标签
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\common\taglib\Next.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\common\taglib;

use think\template\TagLib;

class Next extends TagLib
{
    protected $tags   =  [
        'title' => ['attr' => '', 'close' => 0],
        'url'   => ['attr' => '', 'close' => 0],
        'link'  => ['attr' => '', 'close' => 0],
    ];

    public function tagTitle(): string
    {
        return '{$next.title}';
    }   
    public function tagUrl(): string
    {
        return '{$next.url}';
    }
    public function tagLink(): string
    {
        return '{$next.link}';
    }
}
