<?php
/**
 * @Program: TaoLer 2023/3/11
 * @FilePath: app\admin\controller\user\User.php
 * @Description: User用户管理
 * @LastEditTime: 2023-03-11 10:20:47
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\user;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\model\User as UserModel;
use app\common\lib\Uploads;
use app\common\validate\User as userValidate;
use think\exception\ValidateException;

class User extends AdminController
{

	/**
	 * 浏览
	 *
	 * @return void
	 */
	public function index()
	{
		return View::fetch();
	}
	//用户表

	public function list()
	{
		if(Request::isAjax()){
			$datas = Request::only(['id','name','email','sex','status']);
			$map = array_filter($datas,[$this,'filtrArr']);
			if(!empty($map['id'])) {
			    $map['id'] = (int) $map['id'];
			}

			$user = Db::name('user')->where(['delete_time'=>0])->where($map)->order('id desc')->paginate([
                'list_rows' => input('limit'),
                'page' => input('page')
            ]);
			$count = $user->total();
			$data = [];
			if($count){
				$vipList = [];
				$vipRule = Db::name('user_viprule')->field('id,vip,nick')->select();
				foreach($vipRule as $v) {
					$vipList[] = ['id' => $v['id'], 'vip' => $v['vip'], 'title' => $v['nick']];
				}

				foreach($user as $k => $v){
					$data[] = [
						'id'		=>	$v['id'],
						'username'	=>	$v['name'],
						'nick'		=>	$v['nickname'],
						'avatar'	=>	$v['user_img'],
						'phone'		=>	$v['phone'],
						'email'		=>	$v['email'],
						'sex'		=>	$v['sex'],
						'ip'		=>	$v['last_login_ip'],
						'city'		=>	$v['city'],
						'point'		=>	$v['point'],
						'logintime'	=>	date("Y-m-d H:i:s",$v['last_login_time']),
						'jointime'	=>	date("Y-m-d H:i",$v['create_time']),
						'check'		=>	$v['status'],
						'auth'		=>	$v['auth'],
						'vip'		=> 	$vipList[$v['vip']]['title']
					];
				}
				
				return json(['code'=>0,'msg'=>'ok','count'=>$count, 'data' => $data, 'viplist' => $vipList]);
			}
			return json(['code'=>-1,'msg'=>'没有查询结果！']);			
		}
		return View::fetch();
	}

	protected function getUserVipNick($vip) {

	}
	
	
	//添加用户
	public function add()
	{
		//
		if(Request::isAjax()){
			$data = Request::only(['name','email','user_img','password','phone','sex']);
            try{
                validate(userValidate::class)
                    ->scene('userReg')
                    ->check($data);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return json(['code'=>-1,'msg'=>$e->getError()]);
            }
            $data['create_time'] = time();
            $salt = substr(md5($data['create_time']),-6);
            // 密码
            $data['password'] = md5(substr_replace(md5($data['password']),$salt,0,6));
            try {
                Db::name('user')->save($data);
                $res = ['code'=>0,'msg'=>'添加成功'];
            } catch (\Exception $e) {
                $res = ['code'=>-1, 'msg'=>$e->getMessage()];
            }
		return json($res);
		}
		
		return View::fetch();
	}
	
	//编辑用户
	public function edit()
	{
		if(Request::isAjax()){
			$data = Request::only(['id','name','email','user_img','password','phone','sex']);
            if(empty($data['password'])) {
                unset($data['password']);
            } else {
                $user = Db::name('user')->field('create_time')->find($data['id']);
                $salt = substr(md5($user['create_time']),-6);
                $data['password'] = md5(substr_replace(md5($data['password']),$salt,0,6)); // 密码
            }
			try{
                Db::name('user')->update($data);
                return json(['code'=>0,'msg'=>'编辑成功']);
            } catch (\Exception $e) {
                return json(['code'=> -1,'msg'=>$e->getMessage()]);
            }
		}
		$user = Db::name('user')->find(input('id'));
		View::assign('user',$user);
		return View::fetch();
	}
	
	//删除用户
	public function delete($id)
	{
		$ids = explode(',',$id);
		if(Request::isAjax()){
			$user =UserModel::select($ids);
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

	//登录用户中心
	public function goUserHome() {
		$id = (int)input('id');
		$user_home_url = $this->getUserHome($id);

		return redirect($user_home_url);
	}

	// 编辑用户积分
	public function editField()
	{
		if(Request::isAjax()) {
			$param = Request::param(['id','field','point','note']);
			if($param['field'] == 'point') {
				$data = ['point' => (int)$param['point']];
			} else {
				$data = ['note' => $param['note']];
			}
			$res = Db::name('user')->where('id',(int)$param['id'])->update($data);
			if($res > 0) {
				return json(['code' => 0, 'msg' => '修改成功']);
			}
			return json(['code' => -1, 'msg' => '修改失败']);
		}
	}
	
	// 编辑用户会员等级
	public function editVipLevel()
	{
		if(Request::isAjax()) {
			$param = Request::param(['id','vip']);
			$vipRule = Db::name('user_viprule')->field('vip,nick')->where('nick', $param['vip'])->find();
			$res = Db::name('user')->where('id',(int)$param['id'])->update(['vip' => (int)$vipRule['vip']]);
			if($res > 0) {
				return json(['code' => 0, 'msg' => '修改成功']);
			}
			return json(['code' => -1, 'msg' => '修改失败']);
		}
	}
}
