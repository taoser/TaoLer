<?php
/**
 * @Program: table.css 2023/4/15
 * @FilePath: app\common\taglib\Taoler.php
 * @Description: Taoler.php
 * @LastEditTime: 2023-04-15 11:09:54
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\common\taglib;

use think\template\TagLib;

class Taoler extends TagLib
{
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'nav'      => ['attr' => '', 'close' => 1],
        'snav'      => ['attr' => '', 'close' => 1],
        'gnav'      => ['attr' => '', 'close' => 1],

    ];

    public function tagNav($tag, $content): string
    {
        $parse = '{volist name="cateList" id="nav"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagSnav($tag, $content): string
    {
        $parse = '{volist name="nav.children" id="snav"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagGnav($tag, $content): string
    {
        $parse = '{volist name="snav.children" id="gnav"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }
}