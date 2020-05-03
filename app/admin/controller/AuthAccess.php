<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\exception\ValidateException;
use app\admin\model\AuthGroupAccess;

class AuthAccess extends AdminController
{
	/**
	protected function initialize()
    {
        parent::initialize();
    }
	*/
	//用户组明细
	public function index()
	{
		
		if(Request::isAjax()){
			$data = Request::only(['uid']);
			$map = array_filter($data);
			//var_dump($map);
			$groups = Db::name('auth_group_access')
				->alias('c')
				->join('admin a','c.uid = a.id')
				->join('auth_group g','c.group_id = g.id')
				->field('c.id as id,username,title,c.status as status')
				->where(['c.status'=>1,'c.delete_time'=>0])
				->where($map)
				->select();
			$count = $groups->count();
			$res = [];
			if($count){
				$res = ['code'=>0,'msg'=>'','count'=>$count];			
				foreach($groups as $k => $v){
				$data = ['id'=>$v['id'],'username'=>$v['username'],'title'=>$v['title'],'check'=>$v['status']];
				$res['data'][] = $data;
				}
			} else {
				$res = ['code'=>-1,'msg'=>'没有查询结果！'];
			}
			return json($res);
		}
		$admins = Db::name('admin')->field('id,username')->select();
				
		View::assign('admins',$admins);
		return View::fetch();
	}
	
	//角色添加
	public function add()
	{
		if(Request::isAjax()){
			$data = Request::only(['uid','group_id']);
			//检测重复权限
			$groups = Db::name('auth_group_access')->where('uid',$data['uid'])->column('group_id');
			if(in_array($data['group_id'],$groups)){
				$res = ['code'=>-1,'msg'=>'不能重复添加已存在权限'];
			} else {
				$result = AuthGroupAccess::create($data);
				if($result) {
					$res = ['code'=>0,'msg'=>'添加权限成功'];
				} else {
					$res = ['code'=>-1,'msg'=>'添加权限失败'];
				}
			}
		return json($res);
		}
		
		$admins = Db::name('admin')->field('id,username')->select();
		$auth_groups = Db::name('auth_group')->field('id,title')->select();
		View::assign(['admins'=>$admins,'auth_groups'=>$auth_groups]);
		
		return View::fetch();
	}
	
	//管理员权限编辑
	public function edit($id)
	{
		if(Request::isAjax()){
			$data = Request::only(['id','uid','group_id']);	
			//检测重复权限
			$groups = Db::name('auth_group_access')->where('uid',$data['uid'])->column('group_id');
			if(in_array($data['group_id'],$groups)){
				$res = ['code'=>-1,'msg'=>'不能重复添加已存在权限'];
			} else {
				$result = AuthGroupAccess::where('id',$data['id'])->update(['uid'=>$data['uid'],'group_id'=>$data['group_id']]);
				if($result){
					$res = ['code'=>0,'msg'=>'编辑成功'];
				} else {
					$res = ['code'=>-1,'msg'=>'编辑失败'];
				}
			}
		return json($res);
		}
		
		$access = Db::name('auth_group_access')->group('uid')->find($id);
		$admins = Db::name('admin')->field('id,username')->select();
		$auth_groups = Db::name('auth_group')->field('id,title')->select();
		
		View::assign(['admins'=>$admins,'auth_groups'=>$auth_groups,'access'=>$access]);
		return View::fetch();
	}

	//角色删除
	public function delete($id)
	{
		if(Request::isAjax()){
			$access = AuthGroupAccess::find($id);
			$result = $access->delete();
			
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
		$data = Request::only(['id','status']);

		//获取状态
		$result = AuthGroupAccess::update($data);
		if($result){
			if($data['status'] == 1){
				return json(['code'=>0,'msg'=>'开启权限','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'禁用权限','icon'=>5]);
			}
			
		}else {
			return json(['code'=>-1,'msg'=>'权限执行出错']);
		}
	
	}

	
}
