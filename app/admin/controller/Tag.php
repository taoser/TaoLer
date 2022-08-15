<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2022-08-14 09:39:01
 * @LastEditTime: 2022-08-15 16:12:13
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \TaoLer\app\admin\controller\Tag.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
declare(strict_types=1); 
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\model\Tag as TagModel;

class Tag extends AdminController
{


    // public function __construct(TagModel $tagModel)
    // {
        
    //     $this->tagModel = new TagModel;
    // }

    public function index()
    {
        return View::fetch('index');
    }

    public function list()
    {
        $arr = [];
        $tag = new TagModel;
        $tags = $tag->getTagList();
        // dump($tags);
        if(count($tags)) {
            $arr = ['code'=>0, 'msg'=>'', 'count' => count($tags)];
            foreach($tags as $k=>$v) {
                $arr['data'][] = ['id'=>$v['id'],'name'=>$v['name'], 'ename'=>$v['ename'],'time'=>$v['create_time']];
            }
        } else {
            $arr = ['code'=>-1, 'msg'=>'没有数据'];
        }
        return json($arr);
    }

    public function add()
    {
        if(Request::isAjax()) {
            $data = Request::only(['name','ename']);
            $tagModel = new TagModel;
            $res = $tagModel->saveTag($data);
            if($res == true){
                return json(['code'=>0,'msg'=>'设置成功']);
            }
        }
        return view();
    }

    public function edit()
    {
        $tagModel = new TagModel;
        
        if(Request::isAjax()) {
            $data = Request::only(['name','ename','id']);
            $res =$tagModel::update($data);
            if($res == true){
                return json(['code'=>0,'msg'=>'设置成功']);
            }
        }
        $tag = $tagModel->getTag(input('id'));
        View::assign('tag',$tag);
        return view();
    }

    public function delete()
    {
        if(Request::isPost()) {
            $tagModel = new TagModel;
            $res = $tagModel->delTag(input('id'));
            if($res == true){
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
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
        $tags = $tagModel->getTagList();
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
            $data[] = ['name'=>$tag['name'],'value'=>$tag['id']];
        }
        // halt($data);
        return json(['code'=>0,'data'=>$data]);
    }

}