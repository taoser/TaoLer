<?php
namespace app\admin\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\model\UserViprule;

class Vip extends BaseController
{
	//添加VIP积分规则
	public function add()
	{
		$data = Request::only(['score','vip']);
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
	
	//编辑VIP积分规则
	public function vipEdit($id)
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
		return View::fetch('set/system/vipedit');
	}
	
	//显示VIP规则
	public function vipRule()
	{
		$keys = UserViprule::select();
		$count = $keys->count();
		$res = [];
		if($count){
			$res = ['code'=>0,'msg'=>'','count'=>$count];
			foreach($keys as $k=>$v){
				$res['data'][] = ['id'=>$v['id'],'score'=>$v['score'],'nick'=>$v['nick'],'vip'=>$v['vip'],'ctime'=>$v['create_time']];
			}  
		} else {
				$res = ['code'=>-1,'msg'=>'还没有任何vip等级设置！'];
			}
		return json($res);	
	}

}