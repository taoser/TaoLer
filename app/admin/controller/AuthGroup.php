<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use app\admin\validate\Admin;
use app\admin\model\Admin as adminModel;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\exception\ValidateException;
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthRule as AuthRuleModel;

class AuthGroup extends AdminController
{
	/**
	protected function initialize()
    {
        parent::initialize();
       
    }
	*/
	//角色
	public function list()
	{
		
		if(Request::isAjax()){
			$data = Request::only(['id']);
			$map = array_filter($data);
			$role = Db::name('auth_group')->field('id,title,limits,descr,status')->where('status',1)->where($map)->select();
			$count = $role->count();
			$res = [];
			if($count){
				$res = ['code'=>0,'msg'=>'','count'=>$count];			
				foreach($role as $k => $v){
				$data = ['id'=>$v['id'],'rolename'=>$v['title'],'limits'=>$v['limits'],'descr'=>$v['descr'],'check'=>$v['status']];
				$res['data'][] = $data; 
				}
			} else {
				$res = ['code'=>-1,'msg'=>'没有查询结果！'];
			}
			return json($res);
		}
		$roles = Db::name('auth_group')->field('id,title')->where('status',1)->select();
		View::assign('roles',$roles);
		return View::fetch('role');
	}
	
	//角色添加
	public function roleAdd()
	{
		if(Request::isAjax()){
			$data = Request::param();
			$result = AuthGroupModel::create($data);
			if($result) {
				$res = ['code'=>0,'msg'=>'添加成功'];
			} else {
				$res = ['code'=>-1,'msg'=>'添加失败'];
			}
			return json($res);
		}
		
		$menus = $this->getMenus(1);
		View::assign('menus',$menus);
		
		return View::fetch('roleadd');
	}
	
	//角色编辑
	public function roleEdit()
	{
		
		if(Request::isAjax()){
			$data = Request::param();

/*			
			if(!strpos($data['rules'],'1,2,3,4,5'))
			{
				$data['rules'] = substr_replace($data['rules'],"1,2,3,4,5,",0,0);
			}
*/			
			$rule = AuthGroupModel::update($data);
			if($rule){
				$res = ['code'=>0,'msg'=>'保存成功'];
			} else {
				$res = ['code'=>-1,'msg'=>'保存失败'];
			}
			return json($res);
		}
		
		$menus = $this->getMenus(1);
		View::assign('menus',$menus);
		
		$authGroup = AuthGroupModel::select();
		$auth = AuthGroupModel::find(input('id'));
		$ru = $auth->rules;
		$rus = explode(',',$ru);
			
		View::assign(['authGroup'=>$authGroup,'auth'=>$auth,'ru'=>$ru,'rus'=>$rus]);
		return View::fetch('roleedit');
	}

	//角色删除
	public function roleDel($id)
	{
		if(Request::isAjax()){
			$role =AuthGroupModel::find($id);
			$result = $role->delete();
			
				if($result){
					$res = ['code'=>0,'msg'=>'删除成功'];
				}else{
					$res = ['code'=>-1,'msg'=>'删除失败'];
				}
				return json($res);
			}
	}
	
	//角色审核
	public function check()
	{
		$data = Request::param();

		//获取状态
		$res = Db::name('auth_group')->where('id',$data['id'])->save(['status' => $data['status']]);
		if($res){
			if($data['status'] == 1){
				return json(['code'=>0,'msg'=>'角色审核通过','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'禁用此角色','icon'=>5]);
			}
			
		}else {
			return json(['code'=>-1,'msg'=>'审核出错']);
		}
	
	}

	
}
