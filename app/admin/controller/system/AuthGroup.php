<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\system\AuthGroup.php
 * @Description: AuthGroup
 * @LastEditTime: 2023-03-14 16:43:59
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\system;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\exception\ValidateException;
use app\admin\model\AuthGroup as AuthGroupModel;
use app\admin\model\AuthGroupAccess;
use app\admin\model\AuthRule as AuthRuleModel;
use app\admin\model\Admin as adminModel;
use LDAP\Result;
use think\Response;


class AuthGroup extends AdminBaseController
{
	/**
	 *
	 * @var [type]
	 */
	protected $model = null;

	public function __construct()
    {
        $this->model = new AuthGroupModel;
    }

	/**
	 * 浏览
	 *
	 * @return void
	 */
	public function index()
	{
		$roles = Db::name('auth_group')->field('id,title')->where('status',1)->select();
		View::assign('roles',$roles);
		return View::fetch();
	}
	
	//角色
	public function list()
	{
		
		if(Request::isAjax()){
			$data = Request::only(['id']);
			$map = array_filter($data);
			$role = Db::name('auth_group')->field('id,title,limits,descr,status')->where($map)->select();
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
		return View::fetch('index');
	}
	
	//角色添加
	public function add()
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
		
		$menus = $this->getRoleMenu(1);
		View::assign('menus',$menus);
		
		return View::fetch();
	}
	
	//角色编辑
	public function edit()
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
		$menus = $this->getRoleMenu(1);
		$role = AuthGroupModel::find(input('id'));
		$rus = explode(',',$role->rules);
			
		View::assign(['role'=>$role,'rus'=>$rus,'menus'=>$menus]);
		return View::fetch();
	}

	//角色删除
	public function delete($id)
	{
		$ids = explode(',',$id);
		if(Request::isAjax()){
			$role =AuthGroupModel::select($ids);
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

	/**
	 * 授权
	 *
	 * @return void
	 */
	public function auth()
	{
		$roleId = request()->get('id');
		//
		if(Request::isAjax()) {
			$data = Request::only(['group_id', 'uid']);
			$uidArray = Db::name('auth_group_access')->where('group_id', (int) $data['group_id'])->column('uid');
			
			$newUids = explode(',', $data['uid']);
			try {
				// 1.循环原有的UID跟现在提交过来的UID比较,没有在新uid的，被删除
				foreach($uidArray as $oldUid) {
					if(!in_array($oldUid, $newUids)){
						Db::name('auth_group_access')->where('uid', $oldUid)->delete();
					}
				}
				
				// 2.循环现有的UID再次跟已存在的UID比较，没有的，新增加
				$uids = Db::name('auth_group_access')->where('group_id', (int) $data['group_id'])->column('uid');			
				foreach($newUids as $newUid){
					if(!in_array($newUid, $uids)) {
						Db::name('auth_group_access')->save(['uid' => $newUid, 'group_id' => (int) $data['group_id']]);
					}
				}
				
				return json(['code' => 0, 'msg' => 'ok']);
			} catch (\Exception $e) {
				return json(['code' => -1, 'msg' => $e->getMessage()]);
			}
		
			// $groupAccess = new AuthGroupAccess();
			// $groupAccess->saveAll($array);
		}

		$admin = Db::name('admin')->field('id, username')->select();
		$role = Db::name('auth_group')->field('id,title')->where('id', (int) $roleId)->find();
		$uidAccess = Db::name('auth_group_access')->where('group_id', (int) $roleId)->column('uid');

		View::assign(['role'=>$role, 'admin' => $admin, 'uidAccess' => $uidAccess]);
		return View::fetch();
	}

	
}
