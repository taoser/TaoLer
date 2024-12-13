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
class Comment extends TagLib
{
    /**
     * @var array[]
     */
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'list'      => ['attr' => '', 'close' => 1],
        'count'     => ['attr' => '', 'close' => 0],
        'id'        => ['attr' => '', 'close' => 0],
        'content'   => ['attr' => '', 'close' => 0],
        'time'      => ['attr' => '', 'close' => 0],
        'zan'       => ['attr' => '', 'close' => 0],
        'uid'       => ['attr' => '', 'close' => 0],
        'uname'     => ['attr' => '', 'close' => 0],
        'uimg'      => ['attr' => '', 'close' => 0],
        'ulink'     => ['attr' => '', 'close' => 0],
        'usign'     => ['attr' => '', 'close' => 0],
    ];

    // 评论
    public function tagList(array $tag, string $content): string
    {
        $parse = '{assign name="page" value="$Request.param.page ?? 1" /}';
        $parse .= '<?php if(!isset($__COMMENTS__)) $__COMMENTS__ = \app\facade\comment::getComments($article[\'id\'], $page);';
        $parse .= ' ?>';
        $parse .= '{volist name="__COMMENTS__[\'data\']" id="comment" empty= "还没有内容"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagCount(array $tag, string $content): string
    {
        $parse = '{assign name="page" value="$Request.param.page ?? 1" /}';
        $parse .= '<?php if(!isset($__COMMENTS__)) $__COMMENTS__ = \app\facade\comment::getComments($article[\'id\'], $page);';
        $parse .= ' ?>';
        $parse .= '{$__COMMENTS__.count}';
        return $parse;

    }

    public function tagId()
    {
        return '{$comment.id}';
    }

    public function tagContent()
    {
        return '{$comment.content|raw}';
    }

    public function tagTime()
    {
        return '{$comment.create_time}';
    }

    public function tagZan()
    {
        return '{$comment.zan}';
    }

    public function tagUid()
    {
        return '{$comment.user_id}';
    }

    public function tagUname()
    {
        return '{$comment.user.nickname ?: $comment.user.name}';
    }

    public function tagUimg()
    {
        return '{$comment.user.user_img}';
    }

    public function tagUlink()
    {
        return '{:url("user/home",["id"=>'.'$'.'comment.user_id'.'])->domain(true)}';
    }

    public function tagUsign()
    {
        return '{$comment.user.sign|raw}';
    }

}