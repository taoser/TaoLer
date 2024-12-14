<?php
/**
 * @Program: table.css 2023/4/17
 * @FilePath: app\common\taglib\Snav.php
 * @Description: Snav.php
 * @LastEditTime: 2023-04-17 15:37:30
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\common\taglib;

use think\template\TagLib;

class Snav extends TagLib
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
        return '{$snav.id}';
    }

    public function tagPid(): string
    {
        return '{$snav.pid}';
    }

    public function tagIcon(): string
    {
        return '{$snav.icon}';
    }

    public function tagName($tag): string
    {
        return '{$snav.catename}';
    }

    public function tagEname(): string
    {
        return '{$snav.ename}';
    }

    public function tagTitle(): string
    {
        return '{$snav.catename}';
    }

    public function tagDetpl(): string
    {
        return '{$snav.detpl}';
    }

    public function tagSort(): string
    {
        return '{$snav.sort}';
    }

    public function tagDesc(): string
    {
        return '{$snav.desc}';
    }

    public function tagIs_hot(): string
    {
        return '{$snav.is_hot}';
    }

    public function tagLink(): string
    {
        return '{$snav.url}';
    }

    public function tagChildren(): string
    {
        return '{$snav.children}';
    }
}