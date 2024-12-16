<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2022-07-24 15:58:51
 * @LastEditTime: 2022-08-15 14:52:49
 * @LastEditors: TaoLer
 * @Description: 标签
 * @FilePath: \TaoLer\app\index\controller\Tag.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\index\controller;

use app\facade\Article;
use think\facade\View;
use think\facade\Db;
use app\facade\Tag as TagModel;
use app\facade\TagList;

class Tag extends IndexBaseController
{
    public function initialize()
    {
        parent::initialize();

    }
    //
    public function index()
    {
        return View::fetch();
    }

    // 获取tag列表
    public function List()
    {
        $tagEname = $this->request->param('ename');
        // $page = $this->request->param('page', 1);
        // $limit = $this->request->param('limit', 15);

        $tag = TagModel::getTagByEname($tagEname);


        View::assign('tag', $tag);

        return View::fetch('index');
    }

    /**
     * 所有tag标签
     *
     * @return void
     */
    public function getAllTag()
    {
        $data = [];
        $tagModel = new TagModel;
        $tags = $tagModel::getTagList();
        foreach($tags as $tag) {
            $data[] = ['name'=> $tag['name'], 'value'=> $tag['id']]; 
        }
        return json(['code'=>0,'data'=>$data]);
    }

    public function getArticleTag($id)
    {
        //
        $data = [];
        $artTags = Db::name('taglist')->where('article_id',$id)->select();
        // halt($artTags);
        foreach($artTags as $v) {
            $tag = Db::name('tag')->find($v['tag_id']);
            if(!is_null($tag))
            $data[] = ['name'=>$tag['name'],'value'=>$tag['id']];
        }
        
        return json(['code'=>0,'data'=>$data]);
    }

}

