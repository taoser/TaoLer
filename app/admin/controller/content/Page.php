<?php

namespace app\admin\controller\content;

use app\admin\controller\AdminBaseController;
use Exception;
use think\Request;
use think\facade\View;
use app\facade\Category;
use app\facade\Page as PageEntity;


class Page extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        return View::fetch();
    }

    public function list(Request $request)
    {
        $page = $request->get('page/d',1);
        $limit = $request->get('limit/d', 20);
        
        $list = PageEntity::with(['cate' => function($query) {
            $query->field('id,catename,ename');
        }])->select()->toArray();
  
        if($count = count($list)) {
            return json(['code' => 0, 'msg' => 'ok', 'count' => $count, 'data' => $list]);
        }

        return json(['code' => 1, 'msg' => 'no data']);
    }

    public function add(Request $request)
    {
        if(!$request->isPost()) {
            return View::fetch();
        }

        $data = $request->post(['title','cate_id/d','content','description','keywords']);
        $data['create_time'] = date('Y-m-d H:i:s', time());

        try{
            PageEntity::save($data);
        } catch(Exception $e) {
            return json(['code' => 0, 'msg' => $e->getMessage()]);
        }
        
        return json(['code' => 0, 'msg' => 'ok']);
        
    }

    public function edit(Request $request)
    {

        if(!$request->isPost()) {
            $id = $request->get('id/d');
            $page = PageEntity::field('id,cate_id,title,content,keywords,description,create_time')
            ->with(['cate'=>function($query) {
                $query->field('id,catename');
            }])->find($id);
            
            View::assign('page', $page);
            return View::fetch();
        }

        $data = $request->post(['id','title','cate_id/d','content','description', 'keywords']);
        try{
            PageEntity::update($data);
        } catch(Exception $e) {
            return json(['code' => 1, 'msg' => 'error']);
        } 
        return json(['code' => 0, 'msg' => 'OK']);      

    }

    public function delete()
    {
    }

}