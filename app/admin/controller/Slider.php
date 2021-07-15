<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;
use think\exception\ValidateException;
use app\admin\model\Slider as SliderModel;

class Slider extends AdminController
{
    /**
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        //幻灯列表
		$sliders = SliderModel::select();
		View::assign('slider',$sliders);
		return View::fetch();
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
			$data['slid_start'] =  strtotime($data['slid_start']);
			$data['slid_over'] =  strtotime($data['slid_over']);
			$result = Db::name('slider')->save($data);
			if($result){
				$res = ['code'=>0,'msg'=>'添加成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'添加失败'];
			}
		return json($res);
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
    public function edit($id)
    {
		$slider = Db::name('slider')->find($id);

		if(Request::isAjax()){
			$data = Request::param();
			//var_dump($data);
			$data['slid_start'] =  strtotime($data['slid_start']);
			$data['slid_over'] =  strtotime($data['slid_over']);
			$result = Db::name('slider')->where('id',$id)->save($data);
			if($result){
				$res = ['code'=>0,'msg'=>'编辑成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'编辑失败'];
			}
			return json($res);
		}
		View::assign('slider',$slider);
		
		return View::fetch();
    }

    /**
     * @return \think\response\Json
     */
    public function uploadImg()
    {
        $uploads = new \app\common\lib\Uploads();
        $upRes = $uploads->put('file','slider',1024,'image');
        $slires = $upRes->getData();

		if($slires['status'] == 0){
            $name_path = $slires['url'];
				$res = ['code'=>0,'msg'=>'上传flash成功','src'=>$name_path];
			} else {
				$res = ['code'=>1,'msg'=>'上传错误'];
			}
		return json($res);
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
        //
		$slider = SliderModel::find($id);
		$res = $slider->delete();
		if($res){
			return json(['code'=>0,'msg'=>'删除成功']);
		} else {
			return json(['code'=>-1,'msg'=>'删除失败']);
		}
    }
}
