<?php

namespace app\common\taglib;

use think\template\TagLib;

class Slider extends TagLib
{
    protected $tags = [
        'id'            => ['attr' => '', 'close' => 0],
        'title'         => ['attr' => '', 'close' => 0],
        'url'           => ['attr' => '', 'close' => 0],
        'image'         => ['attr' => '', 'close' => 0],
        'description'   => ['attr' => '', 'close' => 0],
    ];

    public function tagId(): string
    {
        return '{$slider.id}';
    }

    public function tagTitle(): string
    {
        return '{$slider.title}';
    }

    public function tagUrl(): string
    {
        return '{$slider.url}';
    }

    public function tagImage(): string
    {
        return '{$slider.image}';
    }

    public function tagDescription(): string
    {
        return '{$slider.description}';
    }

}