<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\user\Vip.php
 * @Description: Vip
 * @LastEditTime: 2023-03-14 16:48:31
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\user;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\index\model\UserViprule;

class Vip extends AdminBaseController
{

	public function index()
	{
		return View::fetch();
	}

	// VIP列表
	public function list()
	{
		$keys = UserViprule::select();
		$count = $keys->count();
		$data = [];
		if($count){
			foreach($keys as $k=>$v){
				$data[] = [
					'id'=>$v['id'],
					'vip'=>$v['vip'],
					'score'=>$v['score'],
					'nick'=>$v['nick'],
					'postnum'=>$v['postnum'],
					'postpoint'=>$v['postpoint'],
					'refreshnum'=>$v['refreshnum'],
					'refreshpoint'=>$v['refreshpoint'],
					'ctime'=>$v['create_time']
				];
			} 
			return json(['code'=>0,'msg'=>'ok','count'=>$count, 'data' => $data]);
		}
		return json(['code'=>-1,'msg'=>'还没有任何vip等级设置！']);	
	}

	//添加VIP积分规则
	public function add()
	{
		$data = Request::only(['score','vip','nick','postnum','refreshnum']);
		$vip = UserViprule::where('vip',$data['vip'])->find();
		if($vip){
			$res = ['code'=>-1,'msg'=>'vip等级不能重复设置'];
		} else {
			$result = UserViprule::create($data);
			if($result){
				$res = ['code'=>0,'msg'=>'设置vip等级成功'];
			} else {
				$res = ['code'=>-1,'msg'=>'vip保存失败'];
			}
		}
		return json($res);
	}

	//编辑VIP积分规则
	public function edit($id)
	{
		if(Request::isAjax()){
			$data = Request::param();
			$result = UserViprule::update($data);
			if($result){
				$res = ['code'=>0,'msg'=>'编辑成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'编辑失败'];
			}
			return json($res);
		}
		$vip = Db::name('user_viprule')->find($id);
		$level = UserViprule::column('vip');
		View::assign(['vip'=>$vip,'level'=>$level]);
		return View::fetch();
	}
	
	//删除VIP积分规则
	public function delete($id)
	{
		if(Request::isAjax()){
			$user =UserViprule::find($id);
			$result = $user->delete();
			
				if($result){
					return json(['code'=>0,'msg'=>'删除成功']);
				}else{
					return json(['code'=>-1,'msg'=>'删除失败']);
				}
		}
	}

}