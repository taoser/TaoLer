<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-10-15
 * Time: 15:40
 */

namespace app\admin\controller;

use app\common\controller\AdminController;
use app\admin\validate\Admin as AdminValidate;
use app\admin\model\Admin as AdminModel;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Session;
use think\exception\ValidateException;
use app\common\model\User as UserModel;

class Admin extends AdminController
{
	//管理员
	public function index()
	{
		if(Request::isAjax()){
			$admins = Db::name('admin')
			->alias('a')
			->join('auth_group u','a.auth_group_id = u.id')
			->field('a.id as aid,username,mobile,email,title,last_login_ip,a.status as astatus,last_login_time')
			->where('a.delete_time',0)
			->select();
			
			$count = $admins->count();
			if($admins){
				$res = ['code'=>0,'msg'=>'','count'=>$count];
				foreach($admins as $k => $v){
					$data = ['id'=>$v['aid'],'loginname'=>$v['username'],'telphone'=>$v['mobile'],'email'=>$v['email'],'role'=>$v['title'],'ip'=>$v['last_login_ip'],'check'=>$v['astatus'],'logintime'=>date("Y-m-d",$v['last_login_time'])];
					$res['data'][] = $data;
				}
			}
			return json($res);
			}
		return View::fetch();
	}

	
	//管理员审核
	public function check()
	{
		$data = Request::param();

		//获取状态
		$res = Db::name('admin')->where('id',$data['id'])->save(['status' => $data['status']]);
		if($res){
			if($data['status'] == 1){
				return json(['code'=>0,'msg'=>'设置管理员通过','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'管理员已取消','icon'=>5]);
			}
			
		}else {
			return json(['code'=>-1,'msg'=>'审核出错']);
		}
	
	}
	
	//添加管理员
	public function add()
	{
		if(Request::isAjax()){
			$data = Request::param();
			$data['create_time'] = time();
			$salt = substr(md5($data['create_time']),-6);
			$data['password'] = substr_replace(md5($data['password']),$salt,0,6);
			$result = Db::name('admin')->save($data);
			Db::name('auth_group_access')->save(['uid'=>$data['id'],'group_id'=>$data['auth_group_id']]);
			if($result){
				$res = ['code'=>0,'msg'=>'添加成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'添加失败'];
			}
		return json($res);
		}
		$auth_group = Db::name('auth_group')->select();
		View::assign(['auth_group'=>$auth_group]);
		return View::fetch();
	}
	
	//管理员编辑
	public function edit()
	{
		$admin = AdminModel::find(input('id'));
		
		if(Request::isAjax()){
			$data = Request::param();
			if(empty($data['password'])){
				unset($data['password']);
			} else {
				$t =  strtotime($admin['create_time']);
				$salt = substr(md5($t),-6);
				$data['password'] = md5(substr_replace(md5($data['password']),$salt,0,6));
			}
			$data['update_time'] = time();
			$result = $admin->update($data);
			Db::name('auth_group_access')->where('uid',$data['id'])->update(['group_id'=>$data['auth_group_id']]);
			if($result){
				$res = ['code'=>0,'msg'=>'编辑成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'编辑失败'];
			}
			return json($res);
		}
		$auth_group = Db::name('auth_group')->select();
		View::assign(['admin'=>$admin,'auth_group'=>$auth_group]);
		return View::fetch();
	}
	
	//删除管理员
	public function delete($id)
	{
		if(Request::isAjax()){
			$user =AdminModel::find($id);
			$result = $user->delete();
			
				if($result){
					return json(['code'=>0,'msg'=>'删除成功']);
				}else{
					return json(['code'=>-1,'msg'=>'删除失败']);
				}
			}
	}
	//管理员资料更新
	public function info()
    {
		$admin = AdminModel::find(Session::get('admin_id'));
        if(Request::isAjax()){
			$data = Request::only(['nickname','sex','mobile','email','remarks']);
			$result = $admin->save($data);
			if($result){
				$res = ['code'=>0,'msg'=>'更新成功'];
			} else {
				$res = ['code'=>-1,'msg'=>'更新失败'];
			}
		return json($res);
		}	
		View::assign('admin',$admin);
		return View::fetch('set/user/info');
    }

    //改密码
    public function repass()
    {
        //
		$admin = AdminModel::find(Session::get('admin_id'));
        if(Request::isAjax()){
			$data = Request::param();
			$salt = substr(md5(strtotime($admin['create_time'])),-6);
			$pwd = substr_replace(md5($data['oldPassword']),$salt,0,6);
			$data['oldPassword'] = md5($pwd);
			if($admin['password'] != $data['oldPassword']){
				return json(['code'=>-1,'msg'=>'当前密码错误']);
			} elseif($data['password'] != $data['repassword']){
					 return json(['code'=>-1,'msg'=>'两次密码不一致']);
			} else {
				$password = md5(substr_replace(md5($data['password']),$salt,0,6));
				$result = $admin->update([
				'id'	=>	$admin['id'],
				'password' =>	$password
				]);
				if($result){
					$res = ['code'=>0,'msg'=>'更新成功'];
				} else {
					$res = ['code'=>-1,'msg'=>'更新失败'];
				}
				return json($res);
			}

		
		}	
		View::assign('admin',$admin);
		return View::fetch('set/user/repass');
    }
}