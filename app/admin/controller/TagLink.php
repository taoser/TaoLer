<?php
declare(strict_types=1); 
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Config;
use think\facade\Db;
use taoser\SetArr;

class TagLink extends AdminController
{

    public function index()
    {
        return View::fetch();
    }

    public function list()
    {
        $arr = [];
        $tag = input('tag');
        $tags = Config::get('taglink');
        if(count($tags)) {
            $arr = ['code'=>0, 'msg'=>'', 'count' => count($tags)];
            foreach($tags as $k=>$v) {
                $arr['data'][] = ['tag'=>$k, 'link'=>$v];
            }
        } else {
            $arr = ['code'=>-1, 'msg'=>'没有数据'];
        }
        return json($arr);
    }

    public function add()
    {
        if(Request::isAjax()) {
            $data = Request::only(['tag','link']);
            $link = '\''.$data['link'].'\'';
            $arr = [$data['tag'] => $link];
            $res = SetArr::name('taglink')->add($arr);
            if($res == true){
                return json(['code'=>0,'msg'=>'设置成功']);
            }
        }
        return View::fetch();
    }

    public function edit()
    {
        $tag = input('tag');
        if(Request::isAjax()) {
            $data = Request::only(['tag','link']);
            $link = '\''.$data['link'].'\'';
            $arr = [$data['tag'] => $link];
            $res = SetArr::name('taglink')->edit($arr);
            if($res == true){
                return json(['code'=>0,'msg'=>'设置成功']);
            }
        }

        $link = config('taglink.'.$tag);
        View::assign(['tag'=>$tag, 'link'=>$link]);
        return View::fetch();
    }

    public function delete()
    {
        if(Request::isPost()) {
            $tag = input('tag');
            $arr = [$tag => null];
            $res = SetArr::name('taglink')->delete($arr);
            if($res == true){
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
    }

}