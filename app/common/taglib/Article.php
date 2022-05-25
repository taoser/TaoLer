<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2022-05-17 13:08:11
 * @LastEditTime: 2022-05-18 17:22:50
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \TaoLer\app\common\taglib\Article.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
//declare (strict_types = 1);

namespace app\common\taglib;

use think\template\TagLib;
use app\common\model\Article as ArticleModel;

class Article extends TagLib
{
    //
    protected $tags   =  [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'id'            => ['attr' => 'name', 'close' => 0],
        'title'         => ['attr' => 'cid', 'close' => 0], 
        'content'       => ['attr' => 'name', 'close' => 0],
        'istop'         => ['attr' => '', 'close' => 0],
        'aname'         => ['attr' => 'name', 'close' => 0],
        'commentnum'    => ['attr' => 'name', 'close' => 0],
        'comment'       => ['attr' => '', 'close' => 1],
        
    ];

    // public function __construct($tag)
    // {
    //     $id = (int) input('id');        
    //     $page = input('page') ? (int) input('page') : 1;
    //     //parent::__construct();
    //     if(request()->action() == 'detail') {
    //         $article = new ArticleModel();
    //         $this->article = $article->getArtDetail($id);
    //         //dump($this->article);
    //     }
        
    // }

    public function tagId($tag): string
    {
        $parseStr = $this->article['id'];
        //return $parseStr;
        return '<?php echo "' . $parseStr . '"; ?>';
    }

    public function tagTitle(array $tag, string $content): string
    {
        $cid = (int) $this->tpl->get('cid');
        $article = new ArticleModel();
        $art = $article->getArtDetail($cid);
        //dump($art);
        $parseStr = $art['title'];
        //$parseStr = 123;
        //return $parseStr;
        return '<?php echo "' . $parseStr . '"; ?>';
    }

    public function tagContent($tag): string
    {
        $parseStr = $this->article['content'];
        //return $parseStr;
        return '<?php echo "' . $parseStr . '"; ?>';
    }

    public function tagAname($tag): string
    {
        $parseStr = $this->article['user']['nickname'] ?: $this->article['user']['name'];
        // dump($parseStr);
        return $parseStr;
        
    }

    public function tagCommentnum($tag): string
    {
        $parseStr = $this->article['comments_count'];
        // dump($parseStr);
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

    // public function tagComment($tag, $content): string
    // {
    //     //
    //     //$arr   = $this->getComments($this->id, $this->page);
    //     $parse = '<?php ';
    //     $parse .= '$comment_arr=[[1,3,5,7,9],[2,4,6,8,10]];'; // 这里是模拟数据
    //     $parse .= '$__LIST__ = $comment_arr[' . $type . '];';
    /**      $parse .= ' ?>';*/
    //     $parse .= '{volist name="__LIST__" id="' . $name . '"}';
    //     $parse .= $content;
    //     $parse .= '{/volist}';
    //     return $parse;
    // }

}