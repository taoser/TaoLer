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
        'brother'       => ['attr' => '', 'close' => 1],
        'bro_name'      => ['attr' => '', 'close' => 0],
        'bro_ename'     => ['attr' => '', 'close' => 0],
        'bro_url'       => ['attr' => '', 'close' => 0],
        'clist'          => ['attr' => '']
    ];


    public function tagBrother($tag, $content): string
    {
        $parse = '{assign name="ename" value="$Request.param.ename" /}';
        $parse .= '{php}$__brotherCate__ = \app\facade\Category::getBrotherCate($ename);{/php}';
        $parse .= '{volist name="__brotherCate__" id="brother"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;

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

    public function tagClist($tag, $content): string
    {
        //$paras = ;
        return '';
    }


}