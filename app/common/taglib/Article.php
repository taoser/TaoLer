<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2022-05-17 13:08:11
 * @LastEditTime: 2022-07-23 09:39:52
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \TaoLer\app\common\taglib\Article.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
declare (strict_types = 1);

namespace app\common\taglib;

use think\template\TagLib;

class Article extends TagLib
{
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1闭合标签） alias 标签别名 level 嵌套层次
        'id'            => ['attr' => '', 'close' => 0],
        'title'         => ['attr' => '', 'close' => 0],
        'content'       => ['attr' => '', 'close' => 0],
        'author'        => ['attr' => '', 'close' => 0],
        'author_id'     => ['attr' => '', 'close' => 0],
        'author_avatar' => ['attr' => '', 'close' => 0],
        'author_link'   => ['attr' => '', 'close' => 0],
        'pv'            => ['attr' => '', 'close' => 0],
        'comments_num'  => ['attr' => '', 'close' => 0],
        'keywords'      => ['attr' => '', 'close' => 0],
        'description'   => ['attr' => '', 'close' => 0],
        'link'          => ['attr' => '', 'close' => 0],
        'url'           => ['attr' => '', 'close' => 0],
        'time'          => ['attr' => '', 'close' => 0],
        'uptime'        => ['attr' => '', 'close' => 0],
        'is_top'        => ['attr' => '', 'close' => 0],
        'is_good'       => ['attr' => '', 'close' => 0],
        'is_wait'       => ['attr' => '', 'close' => 0],
        'has_image'     => ['attr' => '', 'close' => 0],
        'has_video'     => ['attr' => '', 'close' => 0],
        'master_pic'    => ['attr' => '', 'close' => 0],

        'cate_name'     => ['attr' => '', 'close' => 0],
        'cate_ename'    => ['attr' => '', 'close' => 0],
        'cate_id'       => ['attr' => '', 'close' => 0],

        'field'         => ['attr' => 'name', 'close' => 0],

        'cate'          => ['attr' => 'name', 'close' => 0],
        'user'          => ['attr' => 'name', 'close' => 0],

        'list'          => ['attr' => ''],
        'prev'          => ['attr' => ''],
        'next'          => ['attr' => ''],

        'tag'           => ['attr' => ''],
        'hastag'        => ['attr' => ''],
        'rela'          => ['attr' => ''],
        'rela_count'    => ['attr' => ''],
        'zan'           => ['attr' => ''],
        'zan_count'     => ['attr' => '', 'close' => 0],
        'hotag'         => ['attr' => ''],
        'hotag_count'   => ['attr' => '', 'close' => 0],


        'comment'       => ['attr' => ''],

        'detail'        => ['attr' => 'name', 'close' => 0],
        
        
    ];

    // id
    public function tagId(array $tag, string $content): string
    {
        return '{$article.id}';
    }

    public function tagTitle(array $tag, string $content): string
    {
        return '{$article.title}';
    }

    public function tagContent(array $tag, string $content): string
    {
        return '{$article.content|raw}';
    }

    public function tagAuthor(array $tag, string $content): string
    {
        return '{$article.user.nickname ?: $article.user.name}';
    }

    public function tagAuthor_id(array $tag, string $content): string
    {
        return '{$article.user.id}';
    }

    public function tagAuthor_avatar(array $tag, string $content): string
    {
        return '{$article.user.user_img}';
    }

    public function tagAuthor_link(array $tag, string $content): string
    {
        return '{:url("user_home",["id"=>$article.user.id])->domain(true)}';
    }

    public function tagPv(array $tag, string $content): string
    {
        return '{$article.pv}';
    }

    public function tagComments_num(array $tag, string $content): string
    {
        return '{$article.comments_num}';
    }

    public function tagKeywords(array $tag, string $content): string
    {
        return '{$article.keywords ?: $article.title}';
    }

    public function tagDescription(array $tag, string $content): string
    {
        return '{$article.description}';
    }

    public function tagLink(array $tag, string $content): string
    {
        return '{:url(\'detail\', [\'ename\' => $article.cate.ename,\'id\' => $article.id])->domain(true)}';
    }

    public function tagUrl(array $tag, string $content): string
    {
        return '{$article.url}';
    }

    public function tagTime(array $tag, string $content): string
    {
        return '{$article.create_time}';
    }

    public function tagUptime(array $tag, string $content): string
    {
        return '{$article.update_time}';
    }

    public function tagIs_top(array $tag, string $content): string
    {
        return '{$article.flags.is_top}';
    }

    public function tagIs_good(array $tag, string $content): string
    {
        return '{$article.flags.is_good}';
    }

    public function tagIs_wait(array $tag, string $content): string
    {
        return '{$article.flags.is_wait}';
    }

    public function tagHas_image(array $tag, string $content): string
    {
        return '{$article.has_image}';
    }

    public function tagHas_video(array $tag, string $content): string
    {
        return '{$article.has_video}';
    }

    public function tagMaster_pic(array $tag, string $content): string
    {
        return '{notempty name="article.media.images"}{$article.media.images[0]}{/notempty}';
    }

    public function tagMaster_pic2(array $tag, string $content): string
    {
        return '{$article.master_pic}';
    }

    public function tagCate_name(array $tag, string $content): string
    {
        return '{$article.cate.catename}';
    }

    public function tagCate_ename(array $tag, string $content): string
    {
        return '{$article.cate.ename}';
    }

    public function tagCate_id(array $tag, string $content): string
    {
        return '{$article.cate.id}';
    }


    // field of detail page
    public function tagField($tag): string
    {
        return '{$article.' . $tag['name'] . '}';
    }

    // category info of detail page
    public function tagCate(array $tag, string $content): string
    {
        $result = match($tag['name']) {
            "id" => '{$article.cate_id}',
            "name" => '{$article.cate.catename}',
            "ename" => '{$article.cate.ename}',
            "link" => '{:url(\'cate\',[\'ename\'=>$article.cate.ename])->domain(true)}',
            default => ''
        };

        return $result;
    }

    // user info of detail page
    public function tagUser(array $tag, string $content): string
    {
        $result = match($tag['name']) {
            'id' => '{$article.user.id}',
            'name' => '{$article.user.name}',
            'nick' => '{$article.user.nickname}',
            'avatar' => '{$article.user.user_img}',
            'vip' => '{$article.user.vip}',
            'link'  => '{:url("user_home",["id"=>$article.user.id])->domain(true)}',
            default => ''
        };

        return $result;

        // return '{$article.user.' . $tag['name'] . '}';
    }

    // 详情
    public function tagDetail(array $tag, string $content): string
    {
        $parseStr = '{assign name="id" value="$Request.param.id" /}';
        $parseStr .= '<?php ';
        $parseStr .= '$__article__ = \app\facade\Article::find($id);';
        $parseStr .= ' ?>';
        $parseStr .= '{$__article__.'. $tag['name'] .'}';
        return $parseStr;
    }


    public function tagIs_tops(array $tag, string $content): string
    {
        $parseStr = '{if($article.flags.is_top == \'0\')}';
        $parseStr .= '<span class="layui-btn layui-btn-xs jie-admin" type="set" field="top" rank="1" style="background-color: #ccc" title="置顶">顶</span>';
        $parseStr .= '{else /}';
        $parseStr .= '<span class="layui-btn layui-btn-xs jie-admin" type="set" field="top" rank="0" title="取消置顶">顶</span>';
        $parseStr .= '{/if}';
        return $parseStr;
    }

    // 评论
     public function tagComment2(array $tag, string $content): string
     {
         $parse = '<?php ';
         $parse .= ' ?>';
         $parse .= '{volist name="comments" id="comment" empty= "还没有内容"}';
         $parse .= $content;
         $parse .= '{/volist}';
         return $parse;
     }

    // 评论
    public function tagComment(array $tag, string $content): string
    {
        $parse = '<?php ';
        $parse .= ' ?>';
        $parse .= '{volist name="comments['.'\'data\''.']" id="comment" empty= "还没有内容"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 文章列表
    public function tagList(array $tag, string $content): string
    {
        $type = empty($tag['type']) ? '' : $tag['type'];
        $num = empty($tag['num']) ? 10 : (int)$tag['num'];
        $parse = match($type){
            "top"       => '<?php $__TOPS__ = \app\facade\Article::getTops('.$num.'); ?> {volist name="__TOPS__" id="article"}' .$content. '{/volist}',
            "good"      => '<?php $__GOODS__ = \app\facade\Article::getGoods('.$num.'); ?> {volist name="__GOODS__" id="article"}' .$content. '{/volist}',
            "comment"   => '<?php $__COMMENTS__ = \app\facade\Article::getHotComments('.$num.'); ?> {volist name="__COMMENTS__" id="article"}' .$content. '{/volist}',
            "pv"        => '<?php $__PVS__ = \app\facade\Article::getHotPvs('.$num.'); ?> {volist name="__PVS__" id="article"}' .$content. '{/volist}',
            "index"     => '<?php $__INDEXS__ = \app\facade\Article::getIndexs('.$num.'); ?> {volist name="__INDEXS__" id="article"}' .$content. '{/volist}',
            default     => '{assign name="ename" value="$Request.param.ename ?? \'all\'" /}
                            {assign name="page" value="$Request.param.page ?? 1" /}
                            {assign name="type" value="$Request.param.type ?? \'all\'" /}
                            <?php $__LISTS__ = \app\facade\Category::getArticlesByCategoryEname($ename, $page, $type,'.$num.'); ?> 
                            {volist name="__LISTS__[\'data\']" id="article"}' . $content . '{/volist}'
        };
        
        return $parse;
    }

    // 前一篇
    public function tagPrev(array $tag, string $content): string
    {
        $parse = '<?php $__PREV__ = \app\facade\Article::getPrev($article[\'id\'], $article[\'cate_id\']); ?>';
        $parse .= '{volist name="__PREV__" id="prev"}' . $content . '{/volist}';
            
        return $parse;
    }

    // 后一篇
    public function tagNext(array $tag, string $content): string
    {
        $parse = '<?php $__NEXT__ = \app\facade\Article::getNext($article[\'id\'], $article[\'cate_id\']); ?>';
        $parse .= '{volist name="__NEXT__" id="next"}' . $content . '{/volist}';
            
        return $parse;
    }

    // 相关文章
    public function tagRela(array $tag, string $content): string
    {
        $parse = '<?php if(!isset($__RELA__)) $__RELA__ = \app\facade\Article::getRelationArticle($article[\'id\']); ?>';
        $parse .= '{notempty name="__RELA__"} {volist name="__RELA__" id="rela"}' . $content . '{/volist} {/notempty}';
            
        return $parse;
    }

    // 相关文章统计
    public function tagRela_count(array $tag, string $content): string
    {
        $parse = '<?php if(!isset($__RELA__)) $__RELA__ = \app\facade\Article::getRelationArticle($article[\'id\']); ?>';
        $parse .= '{notempty name="__RELA__"}' . $content . '{/notempty}';
            
        return $parse;
    }

    // 点赞列表
    public function tagZan(array $tag, string $content): string
    {
        $parse = '<?php if(!isset($__ZAN__)) $__ZAN__ = \app\facade\Article::getArticleZanList($article[\'id\']); ?>';
        $parse .= '{volist name="__ZAN__.data" id="zan"} {notempty name="__ZAN__.data"}' . $content . '{/notempty}{/volist}';
            
        return $parse;
    }

    // 点赞列表统计
    public function tagZan_count(array $tag, string $content): string
    {
        $parse = '<?php if(!isset($__ZAN__)) $__ZAN__ = \app\facade\Article::getArticleZanList($article[\'id\']); ?>';
        $parse .= '{$__ZAN__.count}';
            
        return $parse;
    }

    // 标签tag
    public function tagTag(array $tag, string $content): string
    {
        $parse = '<?php if(!isset($__TAGS__)) $__TAGS__ = \app\facade\Article::getTags($article[\'id\']); ?>';
        $parse .= '{volist name="__TAGS__" id="tag"} {notempty name="__TAGS__"}' . $content . '{/notempty}{/volist}';
            
        return $parse;
    }

    // 标签tag
    public function tagHastag(array $tag, string $content): string
    {
        $parse = '<?php if(!isset($__TAGS__)) $__TAGS__ = \app\facade\Article::getTags($article[\'id\']); ?>';
        $parse .= '{notempty name="__TAGS__"}' . $content . '{/notempty}';
            
        return $parse;
    }

    // 热门标签
    public function tagHotag(array $tag, string $content): string
    {
        $parse = '<?php if(!isset($__HOTAG__)) $__HOTAG__ = \app\facade\Tag::getHots(); ?>';
        $parse .= '{volist name="__HOTAG__.data" id="hotag"}{notempty name="__HOTAG__.data"}' . $content . '{/notempty}{/volist}';
            
        return $parse;
    }

    // 热门标签数量
    public function tagHotag_count(array $tag, string $content): string
    {
        $parse = '<?php if(!isset($__HOTAG__)) $__HOTAG__ = \app\facade\Tag::getHots(); ?>';
        $parse .= '{$__HOTAG__.count}';
            
        return $parse;
    }

}