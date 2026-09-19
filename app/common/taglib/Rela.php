<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-16 16:41:17
 * @LastEditTime: 2026-09-19 11:53:29
 * @LastEditors: TaoLer
 * @Description: 上一篇标签
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\common\taglib\Rela.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\common\taglib;

use think\template\TagLib;

class Rela extends TagLib
{
    protected $tags   =  [
        'title' => ['attr' => '', 'close' => 0],
        'url'   => ['attr' => '', 'close' => 0],
        'link'  => ['attr' => '', 'close' => 0],
    ];

    public function tagTitle(): string
    {
        return '{$rela.title}';
    }   
    public function tagUrl(): string
    {
        return '{$rela.url}';
    }
    public function tagLink(): string
    {
        return '{$rela.link}';
    }
}
