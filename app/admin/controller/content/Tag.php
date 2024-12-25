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

namespace app\admin\controller\content;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use app\facade\TagList;
use app\facade\Tag as TagModel;
use think\response\Json;

class Tag extends AdminController
{

    public function index()
    {
        return View::fetch('index');
    }

    /**
     * 数据列表
     *
     * @return void
     */
    public function list(): Json
    {
        $page = $this->request->param('page/d', 1);
        $limit = $this->request->param('limit/d', 10);
    
        $tags = TagModel::getTagList($page, $limit);

        if($tags['data']){
            return json([
                'code'  => 0,
                'msg'   => 'ok',
                'count' => $tags['total'],
                'data'  => $tags['data']
            ]);
        };

        return json([ 'code'=>-1, 'msg'=>'no data']);
    }

    public function add()
    {
        if(Request::isAjax()) {
            $data = Request::only(['name','ename','keywords','description','title']);
            // 把，转换为,并去空格->转为数组->去掉空数组->再转化为带,号的字符串
			$data['keywords'] = implode(',',array_filter(explode(',',trim(str_replace('，',',',$data['keywords'])))));

            $res = TagModel::save($data);
            if($res == true){
                return json(['code'=>0,'msg'=>'设置成功']);
            }
        }
        return view();
    }

    public function edit()
    {

        if(Request::isAjax()) {
            $data = Request::only(['name','ename','id/d','keywords','description','title']);
            // 把，转换为,并去空格->转为数组->去掉空数组->再转化为带,号的字符串
			$data['keywords'] = implode(',',array_filter(explode(',',trim(str_replace('，',',',$data['keywords'])))));
            try{
                TagModel::update($data);
                return json(['code'=>0,'msg'=>'设置成功']);
            } catch(\Exception $e) {
                return json(['code'=>-1,'msg'=>$e->getMessage()]);
            }
        }

        $id = $this->request->param('id/d');
        $tag = TagModel::find($id);

        View::assign('tag',$tag);

        return View::fetch();
    }

    /**
     * 删除
     * @return \think\response\Json
     */
    public function delete()
    {
        $res = TagModel::del((int)input('id'));
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
        return json(['code'=>-1,'msg'=>'删除失败']);
    }

    /**
     * 所有tag标签
     *
     * @return void
     */
    public function getAllTag()
    {
        $data = [];
        $tags = TagModel::getTagList();
        foreach($tags as $tag) {
            $data[] = ['name'=> $tag['name'], 'value'=> $tag['id']]; 
        }
        return json(['code'=>0,'data'=>$data]);
    }

    public function getArticleTag($id)
    {
        $data = [];
        $artTags = TagList::where('article_id', $id)->select();

        foreach($artTags as $v) {
            $tag = TagModel::find($v['tag_id']);
            if(!is_null($tag))
            $data[] = ['name' => $tag['name'],'value' => $tag['id']];
        }
        
        return json(['code'=>0,'data'=>$data]);
    }

}