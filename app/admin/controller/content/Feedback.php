<?php

namespace app\admin\controller\content;

use app\admin\controller\AdminBaseController;
use Exception;
use think\facade\View;
use think\response\Json;
use think\facade\Db;
use think\facade\Request;

class Feedback extends AdminBaseController
{
    public function index()
    {
        return View::fetch();
    }

    public function list() : Json {

        $status = Request::param('status/d', 0);
        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 10);

        $feed = Db::name('feedback')
        ->field('f.id,name,title,content,f.status,f.create_time')
        ->alias('f')
        ->join('user u', 'f.user_id = u.id')
        ->where('f.status', $status)
        ->page($page)
        ->limit($limit)
        ->select();

        if(!$feed->isEmpty()) {
            return json([
                'code' => 0,
                'msg' => 'ok',
                'data' => $feed->all(),
                'count' => count($feed)
            ]);
        }

        return json(['code' => -1, 'msg' => 'no data']);
    }

    public function reply()
    {
        $id = Request::param('id/d');
        $feed = Db::name('feedback')->where('id', $id)->find();
        if(!empty($feed['reply'])) {
            $feed['reply'] = json_decode($feed['reply'], true);
        }

        if(Request::isAjax()){
            $recontent = Request::param('recontent');
            $id = Request::param('id/d');
            
            $feed['reply'][] = [
                'username' => 'admin',
                'recontent' => $recontent,
                'mine'    => false,
                'reply_time' => date('Y-m-d H:i:s', time())
            ];

            try{
                Db::name('feedback')
                ->json(['reply'])
                ->where('id', $id)
                ->update($feed);

                return json(['code' => 0, 'msg' => 'ok']);
                
            } catch(Exception $e) {
                return json(['code' => -1, 'msg' => $e->getMessage()]);
            }
            
        }

        View::assign('feed', $feed);

        return View::fetch();

    }

    public function addReply(array $reply)
    {

    }
}