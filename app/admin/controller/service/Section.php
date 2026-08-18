<?php

namespace app\admin\controller\service;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\Request;
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
    public function list(Request $request): Response
    {
        $id = $request->get('id/d');

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
    public function add(Request $request): Response | string
    {
        //添加模块
        if(!$request->isPost()) {
            return View::fetch();
        }

        $data = $request->post(['type','title','subtitle','alias']);
        $data['create_time'] = date('Y-m-d H:i:s', time());

        try{
            SectionEntity::save($data);
            return json( ['code'=>0,'msg'=>'添加成功']);
        } catch (Exception $e) {
            return json(['code'=>-1,'msg'=>'添加失败']);
        }
    }	
		

    /**
     * 编辑
     *
     * @return void
     */
    public function edit(Request $request): Response | string
    {
        if(!$request->isPost()){
            $id = $request->get('id/d');
            $section = SectionEntity::find($id);
            View::assign('section', $section);
            
            return View::fetch();
		}

        $data = Request::post(['id/d','type','title','subtitle','alias','status/d']);
        $data['update_time'] = date('Y-m-d H:i:s');

        try{
            SectionEntity::update($data);
            return json(['code'=>0,'msg'=>'编辑成功']);
        } catch (Exception $e) {
            return json(['code'=>-1,'msg'=>'编辑失败']);
        }
    }

    /** 
     * 
    */
    public function delete(Request $request): Response
    {
        $id = $request->get('id/d');
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
    public function addSub(Request $request): Response
    {
        if(!$request->isPost()) {
            $section = SectionEntity::select();
            View::assign('section', $section);
            return View::fetch();
        }
        
        $data = $request->post(['section_id','name','icon','image','url','description','sort']);
        $data['create_time'] = date('Y-m-d H:i:s');

        try{
            SectionAccess::save($data);
            return json( ['code'=>0,'msg'=>'添加成功']);
        } catch (Exception $e) {
            return json(['code'=>-1,'msg'=>'添加失败']);
        }
    }

    /**
     * 编辑
     *
     * @return void
     */
    public function editSub(Request $request): Response | string
    {
        if(!$request->isPost()){
            $id = $request->get('id/d');
            $section = SectionEntity::select();
            View::assign('section', $section);

            $sectionSub = SectionAccess::find($id);
            View::assign('sectionSub', $sectionSub);
            
            return View::fetch();
		}

        $data = $request->post(['id/d','name','alias','icon','image','description','sort']);
        $data['update_time'] = date('Y-m-d H:i:s');
        // halt($data);
        try{
            SectionAccess::update($data);
            return json( ['code'=>0,'msg'=>'编辑成功']);
        } catch (Exception $e) {
            return json(['code'=>-1,'msg'=>'编辑失败']);
        }
    }

    /** 
     * 
    */
    public function deleteSub(Request $request): Response
    {
        $id = $request->get('id/d');
        $section = SectionAccess::find($id);

        $res = $section->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }

        return json(['code'=>-1,'msg'=>'删除失败']);
    }

    //审核用户
    public function check(Request $request): Response
    {
        $data = $request->post(['id/d','status/d']);

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
        $uploads = new \app\common\helper\Uploads();
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