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
        'author'        => ['attr' => '', 'close' => 0],
        'author_id'     => ['attr' => '', 'close' => 0],
        'author_avatar' => ['attr' => '', 'close' => 0],
        'author_link' => ['attr' => '', 'close' => 0],
        'pv'            => ['attr' => '', 'close' => 0],
        'title_color'   => ['attr' => '', 'close' => 0],
        'comment_num'   => ['attr' => '', 'close' => 0],
        'comments_count'=> ['attr' => '', 'close' => 0],
        'keywords'      => ['attr' => '', 'close' => 0],
        'description'   => ['attr' => '', 'close' => 0],
        'link'          => ['attr' => '', 'close' => 0],
        'url'           => ['attr' => '', 'close' => 0],
        'time'          => ['attr' => '', 'close' => 0],
        'uptime'        => ['attr' => '', 'close' => 0],
        'is_top'        => ['attr' => '', 'close' => 0],
        'has_img'       => ['attr' => '', 'close' => 0],
        'has_video'     => ['attr' => '', 'close' => 0],

        'cate_name'     => ['attr' => '', 'close' => 0],
        'cate_ename'    => ['attr' => '', 'close' => 0],
        'cate_id'       => ['attr' => '', 'close' => 0],

        'field'         => ['attr' => 'name', 'close' => 0],

        'cate'          => ['attr' => 'name', 'close' => 0],
        'user'          => ['attr' => 'name', 'close' => 0],

        

        'list'          => ['attr' => '', 'close' => 1],


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

    public function tagPv(array $tag, string $content)
    {
        return '{$article.pv}';
    }

    public function tagComment_num(array $tag, string $content): string
    {
        return '{$article.comments_count}';
    }

    public function tagComments_count(array $tag, string $content): string
    {
        return '{$article.comments_count}';
    }

    public function tagTitle_color(array $tag, string $content): string
    {
        return '{$article.title_color  ?: "#333"}';
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
        return '{$article.is_top}';
    }

    public function tagHas_img(array $tag, string $content): string
    {
        return '{$article.has_img}';
    }

    public function tagHas_video(array $tag, string $content): string
    {
        return '{$article.has_video}';
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
        //dump($this->article);
        $parseStr = '{if($article.is_top == 0)}';
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

    public function tagList(array $tag, string $content): string
    {
        $type = isset($tag['type']) ? $tag['type'] : '';
        $parse = match($type){
            "top" => '<?php $__TOPS__ = \app\facade\Article::getTops(); ?> {volist name="__TOPS__" id="article"}' .$content. '{/volist}',
            "hot" => '<?php $__HOTS__ = \app\facade\Article::getHots(); ?> {volist name="__HOTS__" id="article"}' .$content. '{/volist}',
            "index" => '<?php $__INDEXS__ = \app\facade\Article::getIndexs(); ?> {volist name="__INDEXS__" id="article"}' .$content. '{/volist}',
            default => '{assign name="ename" value="$Request.param.ename" /}
                        {assign name="page" value="$Request.param.page ?? 1" /}
                        {assign name="type" value="$Request.param.type ?? \'all\'" /}
                        <?php $__LISTS__ = \app\facade\Category::getArticlesByCategoryEname($ename, $page, $type); ?> 
                        {volist name="__LISTS__[\'data\']" id="article"}' . $content . '{/volist}'
        };

        // $parse = '{assign name="id" value="$Request.param.id" /}';
        // $parse .= '<?php ';
        // $parse .= '$__articles__ = \app\facade\Article::getTops();';
        // $parse .= ' \?\>';
        // $parse .= '{volist name="__articles__" id="article"}';
        // $parse .= $content;
        // $parse .= '{/volist}';
        
        return $parse;

    }

}