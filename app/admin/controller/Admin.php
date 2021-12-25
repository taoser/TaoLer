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
use think\facade\Cookie;
use think\exception\ValidateException;
use app\common\model\User as UserModel;
use taoler\com\Files;

class Admin extends AdminController
{
	//管理员
	public function index()
	{
		if(Request::isAjax()){
		$data = Request::only(['id','username','mobile','email']);
		$map = array_filter($data);
			$admins = Db::name('admin')
			->field('id,username,mobile,email,last_login_ip,status,last_login_time')
			->where('delete_time',0)
			->where($map)
			->select();
			
			$count = $admins->count();
			if($count){
				$res = ['code'=>0,'msg'=>'','count'=>$count];
				foreach($admins as $k => $v){
					$data = ['id'=>$v['id'],'loginname'=>$v['username'],'telphone'=>$v['mobile'],'email'=>$v['email'],'ip'=>$v['last_login_ip'],'check'=>$v['status'],'logintime'=>date("Y-m-d",$v['last_login_time'])];
					$res['data'][] = $data;
				}
			} else {
				$res = ['code'=>-1,'msg'=>'没有查询结果！'];
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
			$data['password'] = md5(substr_replace(md5($data['password']),$salt,0,6));
			$data['status'] = 1;
			//$adminId = Db::name('admin')->insertGetId($data);
			$admin = Db::name('admin')->save($data);
			//Db::name('auth_group_access')->insert(['uid'=>$adminId,'group_id'=>$data['auth_group_id']]);
			if($admin){
				$res = ['code'=>0,'msg'=>'添加成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'添加失败'];
			}
		return json($res);
		}
		//$auth_group = Db::name('auth_group')->select();
		//View::assign(['auth_group'=>$auth_group]);
		return View::fetch();
	}
	
	//管理员编辑
	public function edit($id)
	{
		$admin = AdminModel::find($id);
		
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
			//Db::name('auth_group_access')->where('uid',$data['id'])->update(['group_id'=>$data['auth_group_id']]);
			if($result){
				$res = ['code'=>0,'msg'=>'编辑成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'编辑失败'];
			}
			return json($res);
		}
		//$auth_group = Db::name('auth_group')->select();,'auth_group'=>$auth_group
		View::assign(['admin'=>$admin]);
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
	//基本资料显示
	public function info()
    {
		$admin = AdminModel::find(Session::get('admin_id'));
		$auths = $admin->adminGroup;
		$authname = [];
		foreach($auths as $v){
            $authname[] = $v->title;
        }
        $authGroupTitle = implode('|', $authname);

		View::assign(['admin'=>$admin,'authGroupTitle'=>$authGroupTitle]);
		return View::fetch('set/user/info');
    }
	//管理员资料更新
	public function infoSet()
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
    }

    //显示改密码页面
    public function repass()
    {
		$admin = AdminModel::find(Session::get('admin_id'));
  	
		View::assign('admin',$admin);
		return View::fetch('set/user/repass');
    }
	
    //修改密码
	public function repassSet() 
	{
		if(Request::isAjax()){
			$data = Request::only(['oldPassword','password','repassword']);
			$data['admin_id'] = $this->aid;
			
			$admin = new AdminModel;
			$res = $admin->setpass($data);
			return $res;
		}
	}
	
	//清除缓存Cache
	public function clearCache(){
        $res = $this->clearSysCache();
        if($res){
           return json(['code'=>0,'msg'=>'清除缓存成功']);
        } else {
			return json(['code'=>-1,'msg'=>'清除缓存失败']);
		} 
    }
	
	//退出登陆
	public function logout()
	{
		//清空缓存
		Cookie::delete('adminAuth');
		Session::clear();
		
		return json(['code'=>0,'msg'=>'退出成功' ]);
	}
}