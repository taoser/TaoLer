<?php
/**
 * @Program: table.css 2023/4/17
 * @FilePath: app\common\taglib\Cate.php
 * @Description: Cate.php
 * @LastEditTime: 2023-04-17 21:19:59
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\common\taglib;

use think\template\TagLib;

class Cate extends TagLib
{
    protected $tags = [
        'catename'      => ['attr' => '', 'close' => 0],
        'ename'         => ['attr' => '', 'close' => 0],
        'desc'          => ['attr' => '', 'close' => 0],
        'url'           => ['attr' => '', 'close' => 0],
        'parent'        => ['attr' => '', 'close' => 1],
        'brother'       => ['attr' => '', 'close' => 1],
        'children'      => ['attr' => '', 'close' => 1],
        'bro_name'      => ['attr' => '', 'close' => 0],
        'bro_ename'     => ['attr' => '', 'close' => 0],
        'bro_url'       => ['attr' => '', 'close' => 0],
    ];


    public function tagBrother1($tag, $content): string
    {
        $parse = '{assign name="ename" value="$Request.param.ename" /}';
        $parse .= '{php}$__brotherCate__ = \app\facade\Category::getBrotherCate($ename);{/php}';
        $parse .= '{volist name="__brotherCate__" id="brother"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;

    }

    public function tagParent($tag, $content): string
    {
        $parse = '{assign name="ename" value="$Request.param.ename" /}';
        $parse .= '{php}$__parentCate__ = \app\facade\Category::getSubCate($ename);{/php}';
        $parse .= '{notempty name="__parentCate__"} {volist name="__parentCate__" id="cate"}';
        $parse .= $content;
        $parse .= '{/volist} {/notempty}';
        return $parse;

    }

    public function tagBrother($tag, $content): string
    {
        $parse = '{assign name="ename" value="$Request.param.ename" /}';
        $parse .= '{php}$__brotherCate__ = \app\facade\Category::getBrotherCate($ename);{/php}';
        $parse .= '{notempty name="__brotherCate__"} {volist name="__brotherCate__" id="cate"}';
        $parse .= $content;
        $parse .= '{/volist} {/notempty}';
        return $parse;

    }

    public function tagChildren($tag, $content): string
    {
        $parse = '{assign name="ename" value="$Request.param.ename" /}';
        $parse .= '{php}$__childCate__ = \app\facade\Category::getSubCate($ename);{/php}';
        $parse .= '{notempty name="__childCate__"} {volist name="__childCate__" id="cate"}';
        $parse .= $content;
        $parse .= '{/volist} {/notempty}';
        return $parse;

    }

    public function tagCatename($tag): string
    {
        return '{$cate.catename}';
    }
    public function tagEname($tag): string
    {
        return '{$cate.ename}';
    }
    public function tagDesc($tag): string
    {
        return '{$cate.desc}';
    }

    public function tagUrl($tag): string
    {
        return '{$cate.url}';
    }

    public function tagBro_name($tag): string
    {
        return '{$brother.catename}';
    }

    public function tagBro_ename($tag): string
    {
        return '{$brother.ename}';
    }

    public function tagBro_url($tag): string
    {
        return '{$brother.url}';
    }


}