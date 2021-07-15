<?php

namespace app\admin\controller;

use app\common\controller\AdminController;
use app\admin\validate\Admin;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\model\User as UserModel;
use app\common\lib\Uploads;

class User extends AdminController
{
	/**
	protected function initialize()
    {
        parent::initialize();
       
    }
	*/
	//用户表
	public function list()
	{
		if(Request::isAjax()){
			$datas = Request::only(['id','name','email','sex','status']);
			$map = array_filter($datas,[$this,'filtrArr']);
			$user = Db::name('user')->where(['delete_time'=>0])->where($map)->order('id desc')->paginate(30);
			$count = $user->total();
			$res = [];
			if($count){
				$res = ['code'=>0,'msg'=>'','count'=>$count];
				foreach($user as $k => $v){
				$data = ['id'=>$v['id'],'username'=>$v['name'],'avatar'=>$v['user_img'],'phone'=>$v['phone'],'email'=>$v['email'],'sex'=>$v['sex'],'ip'=>$v['last_login_ip'],'city'=>$v['city'],'logintime'=>date("Y-m-d H:i",$v['last_login_time']),'jointime'=>date("Y-m-d",$v['create_time']),'check'=>$v['status'],'auth'=>$v['auth']];
				$res['data'][] = $data; 
				}
			} else {
				$res = ['code'=>-1,'msg'=>'没有查询结果！'];
			}
			return json($res);
		}
		return View::fetch();
	}
	
	
	//添加用户
	public function userForm()
	{
		//
		if(Request::isAjax()){
			$data = Request::param();
			$result = Db::name('user')->save($data);
			if($result){
				$res = ['code'=>0,'msg'=>'添加成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'添加失败'];
			}
		return json($res);
		}
		
		return View::fetch('userform');
	}
	
	//编辑用户
	public function userEdit()
	{
		if(Request::isAjax()){
			$data = Request::param();
			$result = Db::name('user')->update($data);
			if($result){
				$res = ['code'=>0,'msg'=>'编辑成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'编辑失败'];
			}
			return json($res);
		}
		$user = Db::name('user')->find(input('id'));
		View::assign('user',$user);
		return View::fetch('useredit');
	}
	
	//删除用户
	public function delete($id)
	{
		if(Request::isAjax()){
			$user =UserModel::find($id);
			$result = $user->delete();
			
				if($result){
					return json(['code'=>0,'msg'=>'删除成功']);
				}else{
					return json(['code'=>-1,'msg'=>'删除失败']);
				}
			}
	}
	
	//上传头像
	 public function uploadImg()
    {
		$uploads = new Uploads();
		$upRes = $uploads->put('file','head_pic',2000,'image');
        $userJson = $upRes->getData();
        if($userJson['status'] == 0){
            $res = ['code'=>0,'msg'=>'上传头像成功','src'=>$userJson['url']];
        } else {
            $res = ['code'=>1,'msg'=>'上传错误'];
        }
        return json($res);
    }
	
	
	//审核用户
	public function check()
	{
		$data = Request::only(['id','status']);
		//获取状态
		$res = Db::name('user')->where('id',$data['id'])->save(['status' => $data['status']]);
		if($res){
			if($data['status'] == 1){
				return json(['code'=>0,'msg'=>'用户审核通过','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'禁用用户','icon'=>5]);
			}
			
		}else {
			return json(['code'=>-1,'msg'=>'审核出错']);
		}
	
	}
	
	//超级管理员
	public function auth()
	{
		$data = Request::param();
		$user = Db::name('user')->save($data);
		if($user){
			if($data['auth'] == 1){
				return json(['code'=>0,'msg'=>'设置为超级管理员','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'取消超级管理员','icon'=>5]);
			}
		}else{
			$res = ['code'=>-1,'msg'=>'前台管理员设置失败'];
		} 
		return json($res);
	}
	
	//过滤数组中为空和null的值
	public function filtrArr($arr)
	{
		if($arr === '' || $arr === null){
            return false;
        }
        return true;
	}
	
}
