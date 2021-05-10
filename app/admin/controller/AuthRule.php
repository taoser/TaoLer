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
				$res = ['code'=>0,'msg'=>'ok','count'=>$count];
				
				foreach($auth_rules as $k => $v){
					//$data = $v->getData();
					$data = ['id'=>$v['id'],'pid'=>$v['pid'],'title'=>$v['title'],'url'=>$v['name'],'icon'=>$v['icon'],'status'=>$v['status'],'isMenu'=>$v['ishidden'],'sort'=>$v['sort'],'ctime'=>$v['create_time']];
					$res['data'][] = $data; 
				}
			}			
			return json($res);
		}
		return View::fetch();	
		
	}
	
	//权限树
	public function tree()
	{
		
		//$res = $this->treeTr($this->getMenus());
		//var_dump($res);
		/*
		支持获取三级菜单
		*/	
		$result = $this->getMenus();

			$count = count($result);
			$tree = [];			
			if($result){
				$tree = ['code'=>0,'msg'=>'','count'=>$count];
				
				$res = [];	//auth_rule储存数据表中的表结构
				foreach($result as $k => $v){
					//第一层子权限
					$children = [];
					if(isset($v['children'])){
						
						foreach($v['children'] as $m => $j){
							//第二层子权限
							$chichi = [];
							if(isset($j['children'])){
								//第三层子权限
								foreach($j as $s){
									if(isset($s['children'])){
										$chichi[] = ['id'=>$s['id'],'title'=>$s['title'],'pid'=>$s['pid']];	//子数据的子数据
									}
								}
							}
							
						//if($j['level']  < 3){}
						$children[] = ['id'=>$j['id'],'title'=>$j['title'],'pid'=>$j['pid'],'children'=>$chichi];		//子数据
						}
					}
					
				
				$data[] = ['id'=>$v['id'],'title'=>$v['title'],'pid'=>$v['pid'],'children'=>$children];
				
				}
				
			//构造一个顶级菜单pid=0的数组。把权限放入顶级菜单下子权限中
			$tree['data'][] = ['id'=>0,'title'=>'顶级','pid'=>0,'children'=>$data];
			}

		return json($tree);
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
			$data = Request::param(['id','pid','title','name','icon','sort','ishidden']);
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
