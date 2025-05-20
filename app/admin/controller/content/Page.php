<?php

namespace app\admin\controller\content;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Request;
use app\facade\Category;
use app\facade\Page as PageEntity;
use Exception;

class Page extends AdminBaseController
{
    public function index()
    {
        return View::fetch();
    }

    public function list()
    {
        $page = Request::param('page/d',1);
        $limit = Request::param('limit/d', 20);
        
        $list = PageEntity::with(['cate' => function($query) {
            $query->field('id,catename,ename');
        }])->select()->toArray();
  
        if($count = count($list)) {
            return json(['code' => 0, 'msg' => 'ok', 'count' => $count, 'data' => $list]);
        }

        return json(['code' => 1, 'msg' => 'no data']);
    }

    public function add()
    {
        if(Request::isPost()) {
            $data = Request::param(['title','cate_id','content','description','keywords']);
            $data['create_time'] = date('Y-m-d H:i:s', time());

            try{
                PageEntity::save($data);
            } catch(Exception $e) {
                return json(['code' => 0, 'msg' => $e->getMessage()]);
            }
            
            return json(['code' => 0, 'msg' => 'ok']);
        }
        return View::fetch();

    }

    public function edit()
    {
        if(Request::isPost()) {
            $data = Request::param(['id','title','cate_id','content','description', 'keywords']);
            try{
                PageEntity::update($data);
            } catch(Exception $e) {
                return json(['code' => 1, 'msg' => 'error']);
            } 
            return json(['code' => 0, 'msg' => 'OK']);      
        }

        $id = Request::param('id/d');

        $page = PageEntity::field('id,cate_id,title,content,keywords,description,create_time')->with(['cate'=>function($query) {
            $query->field('id,catename');
        }])->find($id);
        View::assign('page', $page);

        return View::fetch();

    }

    public function delete()
    {
    }

}