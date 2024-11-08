<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\apps\TimeLine.php
 * @Description: TimeLine
 * @LastEditTime: 2023-03-14 10:54:30
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\apps;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\api\model\TimeLine as TimeLineModel;


class TimeLine extends AdminController
{	

    public function index()
    {
		$timeline = TimeLineModel::field('id,timeline_title,timeline_content,create_time')->order('create_time','desc')->select();
		View::assign('timeline',$timeline);
		return View::fetch();
    }
	
	public function add()
	{
		$data = Request::only(['timeline_title','timeline_content']);

		$result = TimeLineModel::create($data);
		if($result){
			$res = ['code'=>0,'msg'=>'添加成功'];
		} else {
			$res = ['code'=>-1,'msg'=>'添加失败'];
		}
		return json($res);
	}
	
	public function edit($id)
	{
		if(Request::isAjax()){
			$data = Request::only(['timeline_title','timeline_content']);
			$result = TimeLineModel::where('id',$id)->update($data);
			if($result){
				$res = ['code'=>0,'msg'=>'成功'];
			} else {
				$res = ['code'=>-1,'msg'=>'失败'];
			}
		return json($res);
		}
		$timeline = TimeLineModel::find($id);
		View::assign('timeline',$timeline);
		return View::fetch();
		
	}
	
	public function delete($id)
	{
		$timeLine = TimeLineModel::find($id);
		$result = $timeLine->delete();
		if($result){
			$res = ['code'=>0,'msg'=>'删除成功'];
		} else {
			$res = ['code'=>-1,'msg'=>'删除失败'];
		}
		return json($res);
	}


}
