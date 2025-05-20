<?php
/**
 * @Program: table.css 2023/4/22
 * @FilePath: addons\ads\taglib\Slider.php
 * @Description: Slider.php
 * @LastEditTime: 2023-04-22 15:44:52
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\common\taglib;

use think\template\TagLib;

class Link extends TagLib
{
    protected $tags = [
        'id'    => ['attr' => '', 'close' => 0],
        'title' => ['attr' => '', 'close' => 0],
        'url'   => ['attr' => '', 'close' => 0],
        'logo'  => ['attr' => '', 'close' => 0],
    ];

    public function tagId(): string
    {
        return '{$link.id}';
    }

    public function tagTitle(): string
    {
        return '{$link.title}';
    }

    public function tagUrl(): string
    {
        return '{$link.url}';
    }

    public function tagLogo(): string
    {
        return '{$link.logo}';
    }

}