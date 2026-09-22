<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2022-07-24 15:58:51
 * @LastEditTime: 2026-09-22 12:30:51
 * @LastEditors: TaoLer
 * @Description: 标签
 * @FilePath: \TaoLer\app\index\controller\Tag.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\index\controller;

use think\Response;
use think\facade\View;
use app\facade\Tag as TagModel;
use app\facade\Taglist;


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

    /**
     * 获取tag列表
     *
     * @return void
     */
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
     * @return Response
     */
    public function getTree(): Response
    {
        $data = [];
        $tags = TagModel::tree();

        if($tags['count'] === 0) {
            return json(['code' => 0, 'data' => []]);
        }

        foreach($tags['data'] as $tag) {
            $data[] = ['name'=> $tag['name'], 'value'=> $tag['id']]; 
        }
        
        return json(['code' => 0, 'data' => $data]);
    }

    /**
     * 所有tag标签
     *
     * @return void
     */
    public function getAllTag(): Json
    {
        $data = [];
        $tags = TagModel::list();

        if($tags['count'] === 0) {
            return json(['code' => 0, 'data' => []]);
        }

        foreach($tags['data'] as $tag) {
            $data[] = ['name'=> $tag['name'], 'value'=> $tag['id']]; 
        }
        
        return json(['code' => 0, 'data' => $data]);
    }

    /**
     * 文章tag
     *
     * @param [type] $id
     * @return void
     */
    public function getArticleTag($id): Json
    {
        $data = [];
        $artTags = Taglist::where('article_id',$id)->select();
        foreach($artTags as $v) {
            $tag = TagModel::find($v['tag_id']);
            if(!is_null($tag))
            $data[] = ['name'=>$tag['name'],'value'=>$tag['id']];
        }
        
        return json(['code'=>0,'data'=>$data]);
    }

}

