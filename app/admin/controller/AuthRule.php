<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\Request;
use think\facade\Db;
use think\facade\View;
use app\admin\model\AuthRule as AuthRuleModel;

class AuthRule extends AdminController
{
    //权限列表
	public function index()
	{
		//获取权限列表
		if(Request::isAjax()){

			$rule = new AuthRuleModel();
			$auth_rules = $rule->authRuleTree();
			$count = count($auth_rules);
			$res = [];
			if($auth_rules){
				$res = ['code'=>0,'msg'=>'','count'=>$count];
				
				foreach($auth_rules as $k => $v){
					//$data = $v->getData();
					$data = ['id'=>$v['id'],'sort'=>$v['sort'],'title'=>str_repeat('---',$v['level']*2).$v['title'],'name'=>$v['name'],'icon'=>$v['icon'],'status'=>$v['status'],'level'=>$v['level']+1,'ishidden'=>$v['ishidden']];
					$res['data'][] = $data; 
				}
			}			
			return json($res);
		}
		return View::fetch();	
		
	}
	//添加权限
	public function add()
	{
		//
		if(Request::isAjax()){
			$data = Request::param();
			$plevel = Db::name('auth_rule')->field('level')->find($data['pid']);
			if($plevel){
				$data['level'] = $plevel['level']+1;
			} else {
				$data['level'] = 0;
			}
			$data['create_time'] = time();
			$list = Db::name('auth_rule')->save($data);
		
			if($list){
				return json(['code'=>0,'msg'=>'添加权限成功']);
			}else{
				return json(['code'=>-1,'msg'=>'添加权限失败']);
			}
		}
		$rule = new AuthRuleModel();
		$auth_rules = $rule->authRuleTree();
		View::assign('AuthRule',$auth_rules);
		return View::fetch();
	}
	
	//权限编辑
	public function edit()
	{
		$rule = new AuthRuleModel();
		
		if(Request::isAjax()){
			$data = Request::param();
			$ruId = $rule->find($data['pid']); //查询出上级ID
			if($ruId){
				$plevel = $ruId->level; //上级level等级
				$data['level'] = $plevel+1;	
			} else {
				$data['level'] = 0;
			}
			
			$zi = $rule->where('pid',$data['id'])->select();//查询出下级

				if($zi){
					$zi->update(['level'=>$data['level']+1]);
				}	

			$save = AuthRuleModel::update($data);

			if($save){
				$res = ['code'=>0,'msg'=>'修改成功'];
			} else {
				$res = ['code'=>-1,'msg'=>'修改失败'];
			}
			return json($res);
		}
		
		$auth_rules = $rule->authRuleTree();
		$rules = $rule->find(input('id'));
		View::assign(['AuthRule'=>$auth_rules,'rules'=>$rules]);
		return View::fetch();
	}
	
	
	//权限开关
	public function check()
	{
		$data = Request::param();

		//获取状态
		$res = Db::name('auth_rule')->where('id',$data['id'])->save(['status' => $data['status']]);
		if($res){
			if($data['status'] == 1){
				return json(['code'=>0,'msg'=>'权限开启','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'权限禁用','icon'=>5]);
			}
			
		}else {
			return json(['code'=>-1,'msg'=>'审核出错']);
		}
	
	}
	
	//菜单显示控制
	public function menuShow()
	{
		$data = Request::param();
		$rules = Db::name('auth_rule')->save($data);
		if($rules){
			if($data['ishidden'] == 1){
				return json(['code'=>0,'msg'=>'设置菜单显示','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'取消菜单显示','icon'=>5]);
			}
		}else{
			$res = ['code'=>-1,'msg'=>'设置失败'];
		} 
		return json($res);
	}
	
	//排序
	public function sort()
	{
		$data = Request::param();
		$rules = Db::name('auth_rule')->save($data);
		if($rules){
			$res = ['code'=>0,'msg'=>'排序成功'];
		}else{
			$res = ['code'=>-1,'msg'=>'排序失败'];
		} 
		return json($res);
	}
	
	public function delete($id)
	{	
		$pids = AuthRuleModel::where('pid',$id)->select();
		if($pids)
		{
			$result = $pids->delete();
		}
		
		$rule = AuthRuleModel::find($id);
		$result = $rule->delete();
		if($result){
			$res = ['code'=>0,'msg'=>'删除成功'];
		} else {
			$res = ['code'=>-1,'msg'=>'删除失败'];
		}
	return json($res);	
	}
	
}
