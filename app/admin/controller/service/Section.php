<?php

namespace app\admin\controller\service;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Request;
use app\facade\Section as SectionEntity;
use app\facade\SectionAccess;
use Exception;

class Section extends AdminBaseController
{
    public function index()
    {
        $section = SectionEntity::select();

        View::assign('section', $section);
        return View::fetch();
    }

    /**
     * 列表
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function list()
    {
        $id = Request::param('id/d');

        $map = [];
        if(!empty($id)) {
            $section_id = $id;
            $map[] = ['section_id', '=', $id];
        }

        $list = SectionAccess::with(['section' => function($query) {
            $query->field('id,title');
        }])
        ->where($map)
        ->select()
        ->toArray();

        if(count($list)) {
            return json(['code' => 0,  'msg' => 'ok', 'data' => $list]);
        }
        
        return json(['code' => 1,  'msg' => 'no data']);
    }

    /**
     * 添加
     *
     * @return void
     */
    public function add()
    {
        //添加模块
        if(Request::isAjax()){
            $data = Request::only(['type','title','subtitle','alias']);
            $data['create_time'] = date('Y-m-d H:i:s', time());

            try{
                SectionEntity::save($data);
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
            $data = Request::only(['id/d','type','title','subtitle','alias','status/d']);
            $data['update_time'] = date('Y-m-d H:i:s', time());

            try{
                SectionEntity::update($data);
                return json(['code'=>0,'msg'=>'编辑成功']);
            } catch (Exception $e) {
                return json(['code'=>-1,'msg'=>'编辑失败']);
            }
		}

        $section = SectionEntity::find($id);
		View::assign('section', $section);
		
		return View::fetch();
    }

    /** 
     * 
    */
    public function delete()
    {
        $id = Request::param('id/d');
        $section = SectionEntity::with('sectionAccess')->find($id);

        $res = $section->together(['sectionAccess'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }

        return json(['code'=>-1,'msg'=>'删除失败']);
    }

    /**
     * 添加
     *
     * @return void
     */
    public function addSub()
    {
        if(Request::isAjax()){
            $data = Request::only(['section_id','name','icon','image','url','description','sort']);
            $data['create_time'] = date('Y-m-d H:i:s', time());

            try{
                SectionAccess::save($data);
                return json( ['code'=>0,'msg'=>'添加成功']);
            } catch (Exception $e) {
                return json(['code'=>-1,'msg'=>'添加失败']);
            }
        }
        
		$section = SectionEntity::select();

        View::assign('section', $section);
		return View::fetch();
    }

    /**
     * 编辑
     *
     * @return void
     */
    public function editSub()
    {
      $id = (int)input('id');

        if(Request::isAjax()){
            $data = Request::only(['id/d','name','alias','icon','image','description','sort']);
            $data['update_time'] = date('Y-m-d H:i:s', time());
            // halt($data);
            try{
                SectionAccess::update($data);
                return json( ['code'=>0,'msg'=>'编辑成功']);
            } catch (Exception $e) {
                return json(['code'=>-1,'msg'=>'编辑失败']);
            }
		}

        $section = SectionEntity::select();
		View::assign('section', $section);

        $sectionSub = SectionAccess::find($id);
		View::assign('sectionSub', $sectionSub);
		
		return View::fetch();
    }

    /** 
     * 
    */
    public function deleteSub()
    {
        $id = Request::param('id/d');
        $section = SectionAccess::find($id);

        $res = $section->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }

        return json(['code'=>-1,'msg'=>'删除失败']);
    }

    //审核用户
    public function check()
    {
        $data = Request::only(['id/d','status/d']);

        //获取状态
        $res = SectionAccess::where('id', $data['id'])->update(['status' => $data['status']]);
        if($res){
            if($data['status'] == 1){
                return json(['code'=>0,'msg'=>'启用成功','icon'=>6]);
            }

            return json(['code'=>0,'msg'=>'已被禁用','icon'=>5]);
        }
        return json(['code'=>-1,'msg'=>'审核出错']);
    }

    /**
     * @return \think\response\Json
     */
    public function uploadImg()
    {
        $uploads = new \app\common\lib\Uploads();
        $upRes = $uploads->put('file','SYS_section',1024,'image');
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