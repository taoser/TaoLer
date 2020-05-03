<?php
namespace app\admin\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\model\UserSignrule;

class Sign extends BaseController
{
	//添加签到积分规则
	public function add()
	{
		$data = Request::only(['days','score']);
		$day = UserSignrule::where('days',$data['days'])->find();
		//$day = Db::name('user_signrule')->where('days',$data['days'])->find();
		if($day){
			$res = ['code'=>-1,'msg'=>'不能重复设置'];
		} else {
			$result = UserSignrule::create($data);
			if($result){
				$res = ['code'=>0,'msg'=>'设置积分成功'];
			} else {
				$res = ['code'=>-1,'msg'=>'保存失败'];
			}
		}
		return json($res);
	}
	
	//删除签到积分规则
	public function delete($id)
	{
		if(Request::isAjax()){
			$user =UserSignrule::find($id);
			$result = $user->delete();
			
				if($result){
					return json(['code'=>0,'msg'=>'删除成功']);
				}else{
					return json(['code'=>-1,'msg'=>'删除失败']);
				}
			}
	}
	
	//编辑签到积分规则
	public function signEdit()
	{
		if(Request::isAjax()){
			$data = Request::param();
			$result = UserSignrule::update($data);
			if($result){
				$res = ['code'=>0,'msg'=>'编辑成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'编辑失败'];
			}
			return json($res);
		}
		$sign = Db::name('user_signrule')->find(input('id'));
		View::assign('sign',$sign);
		return View::fetch('set/system/signedit');
	}
	
	//显示签到积分规则
	public function signRule()
	{
		$keys = UserSignrule::select();
		$count = $keys->count();
		$res = [];
		if($count){
			$res = ['code'=>0,'msg'=>'','count'=>$count];
			foreach($keys as $k=>$v){
				$res['data'][] = ['id'=>$v['id'],'days'=>$v['days'],'score'=>$v['score'],'ctime'=>$v['create_time']];
			}  
		} else {
				$res = ['code'=>-1,'msg'=>'还没有任何积分设置！'];
			}
		return json($res);	
	}

}