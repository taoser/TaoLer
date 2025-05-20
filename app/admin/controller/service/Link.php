<?php

namespace app\admin\controller\service;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Request;
use app\facade\Link as LinkEntity;
use Exception;

class Link extends AdminBaseController
{
    public function index()
    {
        return View::fetch();
    }

    /**
     * 列表
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function list()
    {
        $map = [];

        $limit = Request::param('limit/d', 15);
        $page = Request::param('page/d', 1);

        $count = LinkEntity::where($map)->count();
        $list = LinkEntity::where($map)->page($page)->limit($limit)->select()->toArray();

        $data = [];
        $count = count($list);
        if($count) {
            foreach($list as $k=>$v) {
                $data[] = [
                    'id'    => $v['id'],
                    'title' => $v['title'],
                    'logo' => $v['logo'],
                    'url'   => $v['url'],
                    'status' => $v['status'],
                    'start_time' => isset($v['start_time']) && time() < strtotime($v['start_time']) ? '<span style="color:#1c97f5;">未开始</span>' : $v['start_time'],
                    'end_time' => isset($v['end_time']) && strtotime($v['end_time']) < time() ? '<span style="color:#F00;">已结束</span>' : $v['end_time'],
                    'create_time' => $v['create_time']
                ];
            }

            return json(['code'=>0, 'count'=> $count, 'msg'=>'ok', 'data' => $data]);
        }

      return json(['code' => -1, 'msg' => '还没有数据']);
    }

    /**
     * 添加
     *
     * @return void
     */
    public function add()
    {
        //添加幻灯
        if(Request::isAjax()){
            $data = Request::only(['title','logo','url','start_time','end_time']);

            if(!empty($data['start_time']) && !empty($data['end_time'])) {
                $stime = strtotime($data['start_time']);
                $etime = strtotime($data['end_time']);
                if($etime <= $stime) {
                    return json(['code' => -1, 'msg' => '结束时间不能小于开始时间']);
                }
            }

            try{
                LinkEntity::save($data);
                return json( ['code'=>0,'msg'=>'添加成功']);
            } catch (Exception $e) {
                return json(['code'=>-1,'msg'=>'添加失败']);
            }
        }
		
		return View::fetch();
    }


    /**
     * 编辑
     *
     * @return void
     */
    public function edit()
    {
      $id = (int)input('id');
      

        if(Request::isAjax()){
            $data = Request::only(['id','title','logo','url','start_time','end_time']);

            if(!empty($data['start_time']) && !empty($data['end_time'])) {
                $stime = strtotime($data['start_time']);
                $etime = strtotime($data['end_time']);
                if($etime <= $stime) {
                    return json(['code' => -1, 'msg' => '结束时间不能小于开始时间']);
                }
            }

            if(empty($data['start_time'])) {
                $data['start_time'] = null;
            }

            if(empty($data['end_time'])) {
                $data['end_time'] = null;
            }

            try{
                LinkEntity::update($data);
                return json( ['code'=>0,'msg'=>'编辑成功']);
            } catch (Exception $e) {
                return json(['code'=>-1,'msg'=>'编辑失败']);
            }
		}

        $link = LinkEntity::find($id);
		View::assign('link', $link);
		
		return View::fetch();
    }

    /**
     * @param $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete($id)
    {
      $link = LinkEntity::find($id);
      $res = $link->delete();
      if($res){
        return json(['code'=>0,'msg'=>'删除成功']);
      } else {
        return json(['code'=>-1,'msg'=>'删除失败']);
      }
    }

    //审核用户
    public function check()
    {
        $data = Request::only(['id','status']);

        //获取状态
        $res = LinkEntity::where('id', $data['id'])->update(['status' => $data['status']]);
        if($res){
            if($data['status'] == 1){
                return json(['code'=>0,'msg'=>'启用成功','icon'=>6]);
            } else {
                return json(['code'=>0,'msg'=>'已被禁用','icon'=>5]);
            }
        }
        return json(['code'=>-1,'msg'=>'审核出错']);
    }

    /**
     * @return \think\response\Json
     */
    public function uploadImg()
    {
        $uploads = new \app\common\lib\Uploads();
        $upRes = $uploads->put('file','SYS_link_logo',1024,'image');
        $slires = $upRes->getData();

        if($slires['status'] == 0){
            $name_path = $slires['url'];
            $res = ['code'=>0,'msg'=>'上传成功','src'=>$name_path];
        } else {
            $res = ['code'=>1,'msg'=>'上传错误'];
        }
        return json($res);
    }
}