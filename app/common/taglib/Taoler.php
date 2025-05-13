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
        'nav'       => ['attr' => '', 'close' => 1],
        'snav'      => ['attr' => ''],
        'gnav'      => ['attr' => ''],
        'slide'     => ['attr' => ''],
        'title'     => ['attr' => '', 'close' => 0],
        'name'      => ['attr' => '', 'close' => 0],
        'logo'      => ['attr' => '', 'close' => 0],
        'mlogo'     => ['attr' => '', 'close' => 0],
        'keywords'  => ['attr' => '', 'close' => 0],
        'description'   => ['attr' => '', 'close' => 0],
        'copyright'     => ['attr' => '', 'close' => 0],
        'icp'           => ['attr' => '', 'close' => 0],
        
    ];

    public function tagNav($tag, $content): string
    {
        $id = $tag['id'] ?? 'nav';
        $parse = '{php}$__cate__ = \app\facade\Category::getNav();{/php}';
        $parse .= '{volist name="__cate__" id="'.$id.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagSnav($tag, $content): string
    {
        $id = $tag['id'] ?? 'snav';
        $parse = '{notempty name="nav.children"}';
        $parse .= '{volist name="nav.children" id="'.$id.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{/notempty}';
        return $parse;
    }

    public function tagGnav($tag, $content): string
    {
        $id = $tag['id'] ?? 'gnav';
        $parse = '{notempty name="snav.children"}';
        $parse .= '{volist name="snav.children" id="'.$id.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{/notempty}';
        return $parse;
    }

    // 幻灯片
    public function tagSlide($tag, $content): string
    {
        $type = empty($tag['type']) ? 1 : $tag['type'];
        $num = empty($tag['num']) ? 5 : $tag['num'];

        $parse ='<?php $__SLIDE__ = \app\facade\AdSlide::getSlide(' . $type .',' . $num . '); ?>';
        $parse .= '{volist name="__SLIDE__" id="slide"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    // 网站 标题
    public function tagTitle($tag, $content): string
    {
        return '{$sysInfo.webtitle}';
    }

    // 网站名
    public function tagName($tag, $content): string
    {
        return '{$sysInfo.webname}';
    }
    // logo
    public function tagLogo($tag, $content): string
    {
        return '{$sysInfo.logo}';
    }
    // 移动端logo
    public function tagMlogo($tag, $content): string
    {
        return '{$sysInfo.m_logo}';
    }
    // 关键词
    public function tagKeywords($tag, $content): string
    {
        return '{$sysInfo.keywords}';
    }
    // 描述
    public function tagDescription($tag, $content): string
    {
        return '{$sysInfo.descript}';
    }
    // 版权
    public function tagCopyright($tag, $content): string
    {
        return '{$sysInfo.copyright}';
    }
    // icp备案
    public function tagIcp($tag, $content): string
    {
        return '{$sysInfo.icp}';
    }


}