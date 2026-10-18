<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-07-30 07:19:57
 * @LastEditTime: 2026-10-18 09:06:35
 * @LastEditors: TaoLer
 * @Description: Section.php
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\common\taglib\Section.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\common\taglib;

use think\template\TagLib;

class Section extends TagLib
{
    protected $tags   =  [
        'id'        => ['attr' => '', 'close' => 0],
        'title'     => ['attr' => '', 'close' => 0],
        'subtitle'  => ['attr' => '', 'close' => 0],
        'list'      => ['attr' => '', 'close' => 1],
        'name'      => ['attr' => '', 'close' => 0],
        'icon'      => ['attr' => '', 'close' => 0],
        'image'     => ['attr' => '', 'close' => 0],
        'url'       => ['attr' => '', 'close' => 0],
        'sort'      => ['attr' => '', 'close' => 0],
        'description'   => ['attr' => '', 'close' => 0],
    ];
    public function tagId($tag, $content): string
    {
        return '{$section.id}';
    }
    public function tagTitle($tag, $content): string
    {
        return '{$section.title}';
    }
    public function tagSubtitle($tag, $content): string
    {
        return '{$section.subtitle}';
    }
    public function tagList($tag, $content): string
    {
        $parse = '{volist name="section.sectionAccess" id="section"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }
    public function tagName($tag, $content): string
    {
        return '{$section.name}';
    }
    public function tagIcon($tag, $content): string
    {
        return '{$section.icon}';
    }
    public function tagImage($tag, $content): string
    {
        return '{$section.image}';
    }
    public function tagUrl($tag, $content): string
    {
        return '{$section.url}';
    }
    public function tagDescription($tag, $content): string
    {
        return '{$section.description|raw}';
    }
    public function tagSort($tag, $content): string
    {
        return '{$section.Sort}';
    }
    
}