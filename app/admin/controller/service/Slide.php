<?php

namespace app\admin\controller\service;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Request;
use app\facade\AdSlide;
use Exception;

class Slide extends AdminBaseController
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
        $type = Request::param('type');
        $limit = Request::param('limit/d', 15);
        $page = Request::param('page/d', 1);

        if(!empty($type)) {
            $map[] = ['type', '=', $type];
        }

        $count = AdSlide::where($map)->count();
        $list = AdSlide::where($map)->page($page)->limit($limit)->select()->toArray();

        $data = [];
        $count = count($list);
        if($count) {
            foreach($list as $k=>$v) {
                $data[] = [
                    'id'    => $v['id'],
                    'title' => $v['title'],
                    'image' => $v['image'],
                    'type'  => $v['type'],
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
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function add()
    {
        //添加幻灯
        if(Request::isAjax()){
            $data = Request::param();
            try{
                AdSlide::save($data);
                return json( ['code'=>0,'msg'=>'添加成功']);
            } catch (Exception $e) {
                return json(['code'=>-1,'msg'=>'添加失败']);
            }
        }
		
		return View::fetch();
    }


    /**
     * @param $id
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
      $id = (int)input('id');
      

      if(Request::isAjax()){
        $data = Request::param();
        
            try{
                AdSlide::update($data);
                return json( ['code'=>0,'msg'=>'编辑成功']);
            } catch (Exception $e) {
                return json(['code'=>-1,'msg'=>'编辑失败']);
            }
		}

        $slide = AdSlide::find($id);
		View::assign('slide', $slide);
		
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
      $slider = AdSlide::find($id);
      $res = $slider->delete();
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
        $res = AdSlide::where('id', $data['id'])->save(['status' => $data['status']]);
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
        $upRes = $uploads->put('file','SYS_slider',1024,'image');
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