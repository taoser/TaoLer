<?php
/**
 * @Program: table.css 2023/4/17
 * @FilePath: app\common\taglib\Nav.php
 * @Description: Nav.php
 * @LastEditTime: 2023-04-17 14:25:08
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\common\taglib;

use think\template\TagLib;

class Nav extends TagLib
{
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        //'nav'       => ['attr' => '', 'close' => 1],
        'id'        => ['attr' => '', 'close' => 0],
        'pid'       => ['attr' => '', 'close' => 0],
        'icon'      => ['attr' => '', 'close' => 0],
        'name'      => ['attr' => '', 'close' => 0],
        'ename'     => ['attr' => '', 'close' => 0],
        'title'     => ['attr' => '', 'close' => 0],
        'detpl'     => ['attr' => '', 'close' => 0],
        'sort'      => ['attr' => '', 'close' => 0],
        'desc'      => ['attr' => '', 'close' => 0],
        'is_hot'    => ['attr' => '', 'close' => 0],
        'link'      => ['attr' => '', 'close' => 0],
        'children'  => ['attr' => '', 'close' => 0],

    ];


    public function tagId(): string
    {
        return '{$nav.id}';
    }

    public function tagPid(): string
    {
        return '{$nav.pid}';
    }

    public function tagIcon(): string
    {
        return '{$nav.icon}';
    }

    public function tagName($tag): string
    {
         return '{$nav.catename}';
    }

    public function tagEname(): string
    {
        return '{$nav.ename}';
    }

    public function tagTitle(): string
    {
        return '{:cookie(\'think_lang\') == \'en-us\' ? $nav.ename : $nav.catename}';
    }

    public function tagDetpl(): string
    {
        return '{$nav.detpl}';
    }

    public function tagSort(): string
    {
        return '{$nav.sort}';
    }

    public function tagDesc(): string
    {
        return '{$nav.desc}';
    }

    public function tagIs_hot(): string
    {
        return '{$nav.is_hot}';
    }

    public function tagLink(): string
    {
        return '{$nav.url}';
    }

    public function tagChildren(): string
    {
        return '{$nav.children}';
    }

}