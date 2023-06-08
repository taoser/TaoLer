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
    //
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'id'            => ['attr' => '', 'close' => 0],
        'title'         => ['attr' => '', 'close' => 0],
        'content'       => ['attr' => '', 'close' => 0],
        'auther'        => ['attr' => '', 'close' => 0],
        'pv'            => ['attr' => '', 'close' => 0],
        'title_color'   => ['attr' => '', 'close' => 0],
        'comment_num'   => ['attr' => '', 'close' => 0],
        'keywords'      => ['attr' => '', 'close' => 0],
        'description'   => ['attr' => '', 'close' => 0],
        'link'          => ['attr' => '', 'close' => 0],
        'time'          => ['attr' => '', 'close' => 0],

        'cate'          => ['attr' => 'name', 'close' => 0],
        'user'          => ['attr' => 'name', 'close' => 0],

        'list'          => ['attr' => '', 'close' => 1],


        'comment'       => ['attr' => '', 'close' => 1],

        'istop'         => ['attr' => '', 'close' => 0],

        'detail'      => ['attr' => 'name', 'close' => 0],
//        'detail'      => ['attr' => '', 'close' => 0],
        
    ];

    // id
    public function tagId(): string
    {
        return '{$article.id}';
    }

    public function tagTitle(): string
    {
        return '{$article.title}';
    }

    public function tagContent(): string
    {
        return '{$article.content|raw}';
    }

    public function tagAuther(): string
    {
        return '{$article.user.nickname ?: $article.user.name}';
    }

    public function tagPv()
    {
        return '{$article.pv}';
    }

    public function tagComment_num(): string
    {
        return '{$article.comments_count}';
    }

    public function tagTitle_color(): string
    {
        return '{$article.title_color  ?: "#333"}';
    }

    public function tagKeywords(): string
    {
        return '{$article.keywords ?: $article.title}';
    }

    public function tagDescription(): string
    {
        return '{$article.description}';
    }

    public function tagLink(): string
    {
        return '{$article.url}';
    }

    public function tagTime(): string
    {
        return '{$article.create_time}';
    }

    // 详情分类
    public function tagCate($tag): string
    {
        if($tag['name'] == 'name')
        {
            return '{$article.cate.catename}';
        }

        if($tag['name'] == 'ename')
        {
            return '{$article.cate.ename}';
        }

        if($tag['name'] == 'id')
        {
            return '{$article.cate_id}';
        }

        if($tag['name'] == 'link')
        {
            return '{:url(\'cate\',[\'ename\'=>$article.cate.ename])}';
        }

        return '';
    }

    public function tagUser($tag)
    {
        if($tag['name'] == 'link') {
            return '{:url("user/home",["id"=>'.'$'.'article.user.id'.'])->domain(true)}';
        }
        return '{$article.user.' . $tag['name'] . '}';
    }

    public function tagCateName()
    {
        return '{$article.cate.catename}';
    }

    public function tagCateename()
    {
        return '{$article.cate.id}';
    }

    // 详情
//    public function tagDetail($tag)
//    {
//        return '{$article.' . $tag['name'] . '}';
//    }

    // 详情
    public function tagDetail($tag)
    {
        $parseStr = '{assign name="id" value="$Request.param.id" /}';
        $parseStr .= '<?php ';
        $parseStr .= '$__article__ = \app\facade\Article::find($id);';
        $parseStr .= ' ?>';
        $parseStr .= '{$__article__.'. $tag['name'] .'}';
        return $parseStr;
    }


    public function tagIstop($tag): string
    {
        //dump($this->article);
        $parseStr = '{if($article.is_top == 0)}';
        $parseStr .= '<span class="layui-btn layui-btn-xs jie-admin" type="set" field="top" rank="1" style="background-color: #ccc" title="置顶">顶</span>';
        $parseStr .= '{else /}';
        $parseStr .= '<span class="layui-btn layui-btn-xs jie-admin" type="set" field="top" rank="0" title="取消置顶">顶</span>';
        $parseStr .= '{/if}';
        return $parseStr;
    }

    // 评论
     public function tagComment2($tag, $content): string
     {
         $parse = '<?php ';
         $parse .= ' ?>';
         $parse .= '{volist name="comments" id="comment" empty= "还没有内容"}';
         $parse .= $content;
         $parse .= '{/volist}';
         return $parse;
     }

    // 评论
    public function tagComment($tag, $content): string
    {
        $parse = '<?php ';
        $parse .= ' ?>';
        $parse .= '{volist name="comments['.'\'data\''.']" id="comment" empty= "还没有内容"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 分类列表
    public function tagList($tag, $content): string
    {
        $parse = '{volist name="artList['.'\'data\''.']" id="article" empty= "还没有内容"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

}