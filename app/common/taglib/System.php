<?php
/**
 * @Program: table.css 2023/5/16
 * @FilePath: app\common\taglib\System.php
 * @Description: System.php
 * @LastEditTime: 2023-05-16 21:34:18
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\common\taglib;

use think\template\TagLib;

class System extends TagLib
{
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'webname'       => ['attr' => '', 'close' => 0],
        'webtitle'      => ['attr' => '', 'close' => 0],
        'domain'        => ['attr' => '', 'close' => 0],
        'template'      => ['attr' => '', 'close' => 0],
        'logo'          => ['attr' => '', 'close' => 0],
        'm_logo'        => ['attr' => '', 'close' => 0],
        'cache'         => ['attr' => '', 'close' => 0],
        'upsize'         => ['attr' => '', 'close' => 0],
        'uptype'         => ['attr' => '', 'close' => 0],
        'copyright'      => ['attr' => '', 'close' => 0],
        'keywords'      => ['attr' => '', 'close' => 0],
        'descript'      => ['attr' => '', 'close' => 0],
        'state'         => ['attr' => '', 'close' => 0],
        'is_open'       => ['attr' => '', 'close' => 0],
        'is_comment'    => ['attr' => '', 'close' => 0],
        'is_reg'        => ['attr' => '', 'close' => 0],
        'icp'           => ['attr' => '', 'close' => 0],
        'showlist'      => ['attr' => '', 'close' => 0],
        'blackname'     => ['attr' => '', 'close' => 0],
        'sys_version_num'   => ['attr' => '', 'close' => 0],
        'key'           => ['attr' => '', 'close' => 0],
        'clevel'        => ['attr' => '', 'close' => 0],
        'api_url'       => ['attr' => '', 'close' => 0],
        'base_url'      => ['attr' => '', 'close' => 0],
        'upcheck_url'   => ['attr' => '', 'close' => 0],
        'upgrade_url'   => ['attr' => '', 'close' => 0],
        'create_time'   => ['attr' => '', 'close' => 0],
        'update_time'   => ['attr' => '', 'close' => 0]
    ];

    public function tagWebname(): string
    {
        return '{$sysInfo.webname}';
    }

    public function tagWebtitle(): string
    {
        return '{$sysInfo.webtitle}';
    }

    public function tagDomain(): string
    {
        return '{$sysInfo.domain}';
    }

    public function tagTemplate(): string
    {
        return '{$sysInfo.template}';
    }

    public function tagLogo(): string
    {
        return '{$sysInfo.logo}';
    }

    public function tagMlogo(): string
    {
        return '{$sysInfo.m_logo}';
    }

    public function tagCopyright(): string
    {
        return '{$sysInfo.copyright}';
    }

    public function tagKeywords(): string
    {
        return '{$sysInfo.keywords}';
    }

    public function tagDescript(): string
    {
        return '{$sysInfo.descript}';
    }

    public function tagState(): string
    {
        return '{$sysInfo.state}';
    }

    public function tagIcp(): string
    {
        return '{$sysInfo.icp}';
    }

    public function tagSys_version(): string
    {
        return '{$sysInfo.sys_version_num}';
    }

    public function tagKey(): string
    {
        return '{$sysInfo.key}';
    }

    public function tagCreate_time(): string
    {
        return '{$sysInfo.create_time}';
    }


}