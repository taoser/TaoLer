<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2022-08-14 09:39:01
 * @LastEditTime: 2026-09-22 21:47:34
 * @LastEditors: TaoLer
 * @Description: 标签控制器
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\admin\controller\content\Tag.php
 * Copyright (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
declare(strict_types=1);

namespace app\admin\controller\content;

use think\Request;
use think\Response;
use think\facade\View;
use app\facade\Tag as TagModel;
use app\admin\controller\AdminBaseController;

class Tag extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        return View::fetch('index');
    }

    /**
     * 数据列表
     * @param Request $request
     * @return Response
     */
    public function list(Request $request): Response
    {
        $page = $request->get('page/d', 1);
        $limit = $request->get('limit/d', 10);
    
        $result = TagModel::getList($page, $limit);

        return json(['code'  => 0, 'msg'   => 'ok', 'count' => $result['count'], 'data'  => $result['data']]);
    }

    /**
     * 添加
     * @param Request $request
     * @return Response
     */
    public function add(Request $request): Response
    {
        $data = $request->post(['name','ename','description','title']);
        TagModel::save($data);
        return json([' code' => 0, 'msg' => '添加成功']);
        
    }

    /**
     * 编辑
     * @return void
     */
    public function edit(Request $request)
    {
        if(!$request->isPost()) {
            $id = $request->get('id/d');
            $tag = TagModel::find($id);
            View::assign('tag',$tag);
            return View::fetch();
        }

        $data = $request->post(['id/d','name','ename','description','title']);
        TagModel::update($data);

        return json(['code' => 0, 'msg' => '设置成功']);
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $id = $request->delete('id/d');
        TagModel::del($id);
        
        return json(['code'=>0,'msg'=>'删除成功']);
    }

    /**
     * 标签树
     * @param Request $request
     * @return Response
     */
    public function tree(Request $request): Response
    {
        $result = TagModel::tree();
        $data = [];
        if($result['count'] > 0) {
            foreach($result['data'] as  $v) {
                $data[] = ['name' => $v['name'],'value' => $v['id']];
            }
        }
        return json(['code' => 0, 'msg' => 'ok','data'=>$data]);
    }

}