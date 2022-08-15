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

use app\common\controller\BaseController;
use app\facade\Article;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\model\Slider;
use app\common\model\Tag as TagModel;

class Tag extends BaseController
{
    //
    public function index()
    {
        return View::fetch();
    }

    // 获取tag列表
    public function List()
    {
        //
        $tagEname = Request::param('ename');
        $tagId = Db::name('tag')->where('ename',$tagEname)->value('id');

        $artList = Article::getAllTags($tagId);
        $slider = new Slider();
        //首页右栏
        $ad_comm = $slider->getSliderList(2);
        //	查询热议
        $artHot = Article::getArtHot(10);

        $assign = [
            'tag'=>$tagEname,
            'artList'=>$artList,
            'ad_comm'=>$ad_comm,
            'artHot'=>$artHot,
            'jspage'=>''
        ];
        View::assign($assign);
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

