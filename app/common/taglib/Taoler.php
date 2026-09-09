<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-09 07:17:38
 * @LastEditors: TaoLer
 * @Description: 系统配置项标签库
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\common\taglib\Taoler.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\common\taglib;

use think\template\TagLib;

class Taoler extends TagLib
{
    // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
    protected $tags   =  [
        
        // 系统配置项
        'site_name'      => ['attr' => '', 'close' => 0],
        'title'          => ['attr' => '', 'close' => 0],
        'keywords'       => ['attr' => '', 'close' => 0],
        'description'    => ['attr' => '', 'close' => 0],
        'template'       => ['attr' => '', 'close' => 0],
        'logo'           => ['attr' => '', 'close' => 0],
        'favicon'        => ['attr' => '', 'close' => 0],
        'icp'            => ['attr' => '', 'close' => 0],
        'copyright'      => ['attr' => '', 'close' => 0],
        'domain'         => ['attr' => '', 'close' => 0],
        'admin_module'   => ['attr' => '', 'close' => 0],
        'system_debug'   => ['attr' => '', 'close' => 0],
        'website_switch' => ['attr' => '', 'close' => 0],
        'api_switch'     => ['attr' => '', 'close' => 0],
        

        // 导航
        'nav'       => ['attr' => '', 'close' => 1],
        'snav'      => ['attr' => ''],
        'gnav'      => ['attr' => ''],
        
        // 幻灯片和链接
        'slide'     => ['attr' => ''],
        'link'      => ['attr' => ''],

        //
        'section'   => ['attr' => 'name,num', 'close' => 1],
        
    ];

    //=====================================================

    // 网站名称
    public function tagSite_name(array $tag): string
    {
        return '{:system_config("site_name")}';
    }

    // 网站标题
    public function tagTitle(array $tag): string
    {
        return '{:system_config("title")}';
    }

    // 关键词
    public function tagKeywords(array $tag): string
    {
        return '{:system_config("keywords")}';
    }

    // 描述
    public function tagDescription(array $tag): string
    {
        return '{:system_config("description")}';
    }

    // favicon
    public function tagFavicon(array $tag): string
    {
        return '{:system_config("favicon")}';
    }

    // logo
    public function tagLogo(array $tag): string
    {
        return '{:system_config("logo")}';
    }

    // 网站名
    public function tagAdmin_module(array $tag): string
    {
        return '{:system_config("admin_module")}';
    }
    
    // 版权
    public function tagCopyright(array $tag): string
    {
        return '{$systemConfig.copyright}';
    }
    // icp备案
    public function tagIcp(array $tag): string
    {
        return '{:system_config("icp")}';
    }

    // 域名
    public function tagDomain(array $tag): string
    {
        return '{:system_config("domain")}';
    }

    // 系统调试
    public function tagSystem_debug(array $tag): string
    {
        return '{:system_config("system_debug")}';
    }

    // 网站开关
    public function tagSite_switch(array $tag): string
    {
        return '{:system_config("site_switch")}';
    }

    // API开关
    public function tagApi_switch(array $tag): string
    {
        return '{:system_config("api_switch")}';
    }

    //=====================================================

    // 导航
    public function tagNav(array $tag, string $content): string
    {
        $id = $tag['id'] ?? 'nav';
        $parse = '{php}$__CATEGORY__ = \app\facade\Category::getNav();{/php}';
        $parse .= '{notempty name="__CATEGORY__"}';
        $parse .= '{volist name="__CATEGORY__" id="'.$id.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{/notempty}';
        return $parse;
    }

    public function tagSnav(array $tag, string $content): string
    {
        $id = $tag['id'] ?? 'snav';
        $parse = '{notempty name="nav.children"}';
        $parse .= '{volist name="nav.children" id="'.$id.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{/notempty}';
        return $parse;
    }

    public function tagGnav(array $tag, string $content): string
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

    // 友情链接 and 合作伙伴
    public function tagLink($tag, $content): string
    {
        $num = empty($tag['num']) ? 10 : $tag['num'];
        $parse ='<?php $__LINK__ = \app\facade\Link::getLink(' . $num . '); ?>';
        $parse .= '{volist name="__LINK__" id="link"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    //section
    public function tagSection($tag, $content): string
    {
        $name = !empty($tag['name']) ? $tag['name'] : '';
        $num = !empty($tag['num']) ? $tag['num'] : 10;

        $parse ='<?php $section = \app\facade\Section::getSection(\'' . $name . '\'); ?>';
        $parse .='{notempty name="section"}';
        $parse .='<?php $__SECTIONSUB__ = \app\facade\SectionAccess::getSectionAccess("' . $name . '",' . $num . '); ?>';
        $parse .= $content;
        $parse .= '{/notempty}';
    
        return $parse;
    }


}