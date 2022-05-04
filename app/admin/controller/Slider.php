<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use app\common\model\Slider as SliderModel;

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
		  return View::fetch();
    }

    /**
     * 链接列表
     *
     * @return void
     */
    public function list()
    {
      $list = [];
      $type = input('slid_type');
      $limit = input('limit');
      $page = input('page');
      if($type) {
        $datas = SliderModel::where('slid_type',$type)->where('')->paginate([
          'list_rows'=> $limit,
          'page'=>$page
        ]);
      } else {
        $datas = SliderModel::paginate([
          'list_rows'=> $limit,
          'page'=>$page
        ]);
      }

      if(count($datas)) {
        $list = ['code'=>0,'msg'=>'获取数据成功'];
        foreach($datas as $k=>$v) {
          $list['data'][] = [
            'id'=>$v['id'],
            'slid_name'=>$v['slid_name'],
            'slid_img' =>$v['slid_img'],
            'slid_type'=>$v['slid_type'],
            'slid_href'=>$v['slid_href'],
            'slid_color'=>$v['slid_color'],
            'slid_start'=> time() < $v['slid_start'] ? '<span style="color:#1c97f5;">未开始</span>' : date('Y-m-d H:i',$v['slid_start']),
            'slid_over'=> time() > $v['slid_over'] ? '<span style="color:#F00;">已结束</span>' : date('Y-m-d H:i',$v['slid_over']),
            'slid_status'=> $v['slid_status'] ? '正常' : '禁止'
          ];
        }
        return json($list);
      } else {
        return json(['code'=>-1,'msg'=>'还没有数据']);
      }
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
      $slid = new SliderModel();
      $result = $slid->add($data);
			if($result == 1){
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
    public function edit()
    {
      $id = (int)input('id');
      $slider = Db::name('slider')->find($id);

      if(Request::isAjax()){
        $data = Request::param();
        $data['slid_start'] =  strtotime($data['slid_start']);
        $data['slid_over'] =  strtotime($data['slid_over']);
        $slid = new SliderModel();
        $result = $slid->edit($data);
        if($result == 1){
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
      $slider = SliderModel::find($id);
      $res = $slider->delete();
      if($res){
        return json(['code'=>0,'msg'=>'删除成功']);
      } else {
        return json(['code'=>-1,'msg'=>'删除失败']);
      }
    }
}
