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

class Slide extends TagLib
{
    protected $tags = [
        'id'    => ['attr' => '', 'close' => 0],
        'title' => ['attr' => '', 'close' => 0],
        'url'   => ['attr' => '', 'close' => 0],
        'image' => ['attr' => '', 'close' => 0],
        'description'   => ['attr' => '', 'close' => 0],
    ];

    public function tagId(): string
    {
        return '{$slide.id}';
    }

    public function tagTitle(): string
    {
        return '{$slide.title}';
    }

    public function tagUrl(): string
    {
        return '{$slide.url}';
    }

    public function tagImage(): string
    {
        return '{$slide.image}';
    }

    public function tagDescription(): string
    {
        return '{$slide.image}';
    }

}