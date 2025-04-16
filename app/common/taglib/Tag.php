<?php
/*
 * @Program: table.css 2023/4/16
 * @FilePath: app\common\taglib\Comment.php
 * @Description: Comment.php 评论标签
 * @LastEditTime: 2023-04-16 11:37:01
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\common\taglib;

use think\template\TagLib;

/**
 * 评论内容
 */
class Tag extends TagLib
{
    /**
     * @var array[]
     */
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'list'      => ['attr' => '', 'close' => 1],
        'count'     => ['attr' => '', 'close' => 0],
        'id'        => ['attr' => '', 'close' => 0],
        'name'      => ['attr' => '', 'close' => 0],
        'keywords'  => ['attr' => '', 'close' => 0],
        'description'=> ['attr' => '', 'close' => 0],
        'title'      => ['attr' => '', 'close' => 0],
    ];

    // 评论
    public function tagList(array $tag, string $content): string
    {
        $num = isset($tag['num']) ? (int)$tag['num'] : 10;
        $parse = '{notpresent name="ename"}{assign name="ename" value="$Request.param.ename" /}{/notpresent}';
        $parse .= '{notpresent name="page"}{assign name="page" value="$Request.param.page ?? 1" /}{/notpresent}';
        $parse .= '<?php if(!isset($__TAGLIST__)) $__TAGLIST__ = \app\facade\Taglist::getArticleList($ename,$page,'. $num .');';
        $parse .= ' ?>';
        $parse .= '{volist name="__TAGLIST__.data" id="article" empty= "还没有内容"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagCount(array $tag, string $content)
    {
        $parse = '{notpresent name="ename"}{assign name="ename" value="$Request.param.ename" /}{/notpresent}';
        $parse .= '{notpresent name="page"}{assign name="page" value="$Request.param.page ?? 1" /}{/notpresent}';
        $parse .= '<?php if(!isset($__TAGLIST__)) $__TAGLIST__ = \app\facade\Taglist::getArticleList($ename,$page); ?>';
        $parse .= '{$__TAGLIST__.count}';

        return $parse;
    }

    public function tagId(array $tag, string $content): string
    {
        return '{$tag.id}';
    }

    public function tagName(array $tag, string $content): string
    {
        return '{$tag.name}';
    }

    public function tagTitle(array $tag, string $content): string
    {
        return '{$tag.title}';
    }

    public function tagKeywords(array $tag, string $content): string
    {
        return '{$tag.keywords}';
    }

    public function tagDescription(array $tag, string $content): string
    {
        return '{$tag.description}';
    }
}