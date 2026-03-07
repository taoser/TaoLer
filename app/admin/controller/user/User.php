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

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Session;
use think\facade\Cookie;
use app\facade\Article;
use app\facade\Comment;
use app\facade\User as UserModel;
use app\common\lib\Uploads;
use app\common\validate\User as userValidate;
use think\exception\ValidateException;

use Exception;
use think\response\Json;

class User extends AdminBaseController
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
	public function list(): Json
	{
		$page = Request::param('page/d', 1);
		$limit = Request::param('limit/d', 10);

		$datas = Request::param(['id','name','email','sex','status']);

		$map = $this->getParamFilter($datas);

		$query = Db::name('user')->alias('u')->join('user_viprule v', 'u.vip = v.vip');

		if(!empty($map['id'])) {
			$query->where('u.id', $map['id']);
			unset($map['id']);
		}

		$user = $query
		->field('u.id,name,nickname,user_img,phone,email,sex,last_login_ip,city,point,last_login_time,u.create_time,status,auth,note,v.nick as vipnick,u.vip')
		->where($map)
		->where('u.delete_time', 0)
		->order('u.id desc')
		->page($page, $limit)
		->select();

		if($user->isEmpty()){
			return json(['code'=>-1,'msg'=>'没有查询结果！']);
		}
		
		foreach($user as &$v){
			$v['create_time']	=	date("Y-m-d H:i",$v['create_time']);
		}
		unset($v);

		$vipList = Db::name('user_viprule')->field('id,vip,nick as title')->select();

		return json(['code'=>0,'msg'=>'ok','count' => count($user), 'data' => $user, 'viplist' => $vipList]);
		
	}
	
	//添加用户
	public function add()
	{
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
			$data = Request::only(['id/d','name','email','user_img','password','phone','sex']);
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
	public function delete()
	{
		$id = Request::param('id');
		$ids = explode(',', $id);
		
		$user = UserModel::select($ids);
		$result = $user->delete();
		
		if($result){
			return json(['code'=>0,'msg'=>'删除成功']);
		}
		return json(['code'=>-1,'msg'=>'删除失败']);
	}

	//清除用户资源
	public function clear()
	{
		$id = (int)input('id');
		try{

			$articleCount = Article::where('user_id', $id)->count();
			$commentCount = Comment::where('user_id', $id)->count();

			if($articleCount) {
				Article::destroy(function($query) use($id){
					$query->where('user_id','=', $id);
				});
			}

			if($commentCount) {
				Comment::destroy(function($query) use($id){
					$query->where('user_id','=', $id);
				});
			}
			
		} catch(Exception $e) {
			return json(['code'=>-1,'msg'=>'清空资源失败']);
		}
			
		return json(['code'=>0,'msg'=>'清空资源成功']);
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
		}

		return json(['code'=>-1,'msg'=>'审核出错']);
	}
	
	//超级管理员
	public function auth()
	{
		$data = Request::param(['id/d', 'auth']);

		$user = Db::name('user')->save($data);
		if($user){
			if($data['auth'] == 1){
				return json(['code'=>0,'msg'=>'设置为超级管理员','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'取消超级管理员','icon'=>5]);
			}
		}
		return json(['code'=>-1,'msg'=>'前台管理员设置失败']);
	}

	//登录用户中心
	public function goUserHome() {
		$id = (int)input('id');
		Session::delete('user_name');
		Session::delete('user_id');
		Cookie::delete('auth');
		$user_home_url = $this->getUserHome($id);

		return redirect($user_home_url);
	}

	// 编辑用户积分
	public function editField()
	{
		$param = Request::param(['id/d','field','point/d','note']);
		if($param['field'] == 'point') {
			$data = ['point' => $param['point']];
		} else {
			$data = ['note' => $param['note']];
		}

		$res = Db::name('user')->where('id', $param['id'])->update($data);
		if($res) {
			return json(['code' => 0, 'msg' => '修改成功']);
		}

		return json(['code' => -1, 'msg' => '修改失败']);
	}
	
	// 编辑用户会员等级
	public function editVipLevel()
	{
		$param = Request::param(['id/d','vip/d']);
		$res = Db::name('user')
		->where('id', $param['id'])
		->update(['vip' => $param['vip']]);
		
		if($res) {
			return json(['code' => 0, 'msg' => '修改成功']);
		}

		return json(['code' => -1, 'msg' => '修改失败']);
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
