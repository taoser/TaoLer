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
use Exception;
use think\exception\ValidateException;
use think\Request;
use think\Response;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Config;
use app\facade\Article;
use app\facade\Comment;
use app\facade\User as UserModel;
use app\common\helper\Uploads;
use app\common\validate\User as userValidate;

use app\common\facade\HttpHelper;
use app\common\helper\PasswordHash;
use think\response\Json;

class User extends AdminBaseController
{
	/**
	 * 用户模型	
	 * @var UserModel
	 * @access private
	 */
	private $model;

	public function initialize()
	{
		parent::initialize();

		$this->model = new UserModel();
	}

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
	public function list(Request $request): Response
	{
		$page = $request->get('page/d', 1);
		$limit = $request->get('limit/d', 10);
		$param = $request->get(['id/d','name','email','sex','status']);

		$query = Db::name('user')
		->alias('u')
		->join('user_viprule v', 'u.vip = v.vip')
		->field('u.id,name,nickname,avatar,phone,email,sex,last_login_ip,city,point,last_login_time,u.create_time,status,auth,note,v.nick as vipnick,u.vip');

		if(!empty($param['id'])) {
			$query->where('u.id', $param['id']);
		}
		if(!empty($param['name'])){
			$query->where('u.name|u.nickname', 'like', "%{$param['name']}%");
		}
		if(!empty($param['email'])){
			$query->where('u.email', 'like', "%{$param['email']}%");
		}
		if(!empty($param['sex'])){
			$query->where('u.sex', $param['sex']);
		}
		if(!empty($param['status'])){
			$query->where('u.status', $param['status']);
		}

		$user = $query
		->whereNull('u.delete_time')
		->order('u.id desc');

		$count = $user->count();

		$users = $user->page($page, $limit)
		->select()
		->toArray();

		if(!$count){
			return json(['code' => -1, 'msg'=>'no data']);
		}

		$vipList = Db::name('user_viprule')->field('id,vip,nick as title')->select();

		return json(['code' => 0, 'msg' => 'ok', 'count' => $count, 'data' => $users, 'viplist' => $vipList]);
		
	}
	
	//添加用户
	public function add(Request $request): Response | string
	{
		if(!$request->isPost()){
			return View::fetch();
		}

		$data = $request->post(['name','email','avatar','password','phone','sex']);
		try{
			validate(userValidate::class)->scene('userReg')->check($data);

			// 密码
			$data['password'] = PasswordHash::make($data['password']);

			$this->model::save($data);

			return json(['code'=>0,'msg'=>'添加成功']);

		} catch (ValidateException $e) {
			return json(['code'=>-1,'msg'=>$e->getError()]);
		} catch (\Exception $e) {
			return json(['code'=>-1, 'msg'=>$e->getMessage()]);
		}

	}
	
	//编辑用户
	public function edit(Request $request): Response | string
	{
		if(!$request->isPost()){
			$id = $request->get('id/d');
			$user = $this->model::find($id);
			View::assign('user',$user);
			return View::fetch();
		}
		
		$data = $request->post(['id/d','name','email','avatar','password','phone','sex']);

		if(empty($data['password'])) {
			unset($data['password']);
		} else {
			$data['password'] = PasswordHash::make($data['password']);
		}

		try{
			Db::name('user')->update($data);
			return json(['code'=>0,'msg'=>'编辑成功']);
		} catch (\Exception $e) {
			return json(['code'=> -1,'msg'=>$e->getMessage()]);
		}
		
	}
	
	//删除用户
	public function delete(Request $request): Response
	{
		$id = $request->get('id');
		$ids = explode(',', $id);
		
		$result = $this->model::destroy($ids);
		
		if($result){
			return json(['code'=>0,'msg'=>'删除成功']);
		}
		return json(['code'=>-1,'msg'=>'删除失败']);
	}

	//清除用户资源
	public function clear(Request $request): Response
	{
		$id = $request->get('id');
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
			return json(['code'=>0,'msg'=>'清空资源成功']);
		} catch(Exception $e) {
			return json(['code'=>-1,'msg'=>'清空资源失败']);
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
	public function check(Request $request): Response
	{
		$data = $request->post(['id','status']);
		//获取状态
		$res = Db::name('user')->save(['status' => $data['status'], 'id' => $data['id']]);
		if($res){
			return json(['code'=>0,'msg'=> $data['status'] == 1 ? '审核通过' : '已被禁用', 'icon'=>6]);
		}

		return json(['code' => -1, 'msg' => '审核出错']);
	}
	
	//超级管理员
	public function auth(Request $request): Response
	{
		$data = $request->post(['id/d', 'auth']);

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
	public function goUserHome(Request $request): Response
	 {
		$id = $request->get('id/d');
		Session::delete('user_name');
		Session::delete('user_id');
		Cookie::delete('auth');

		$user = Db::name('user')->field('id,name')->find($id);
		$salt = Config::get('taoler.salt');
		$auth = md5($user['name'].$salt).":".$user['id'];
    	Cookie::set('auth',$auth,604800);

		$user_home_url = (string) url('user_home', ['id' => $id]);

		return redirect($user_home_url);
	}

	// 编辑用户积分
	public function editField(Request $request): Response
	{
		$param = $request->post(['id/d','field','point/d','note']);
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
	public function editVipLevel(Request $request): Response
	{
		$param = $request->post(['id/d','vip/d']);
		$res = Db::name('user')
		->where('id', $param['id'])
		->update(['vip' => $param['vip']]);
		
		if($res) {
			return json(['code' => 0, 'msg' => '修改成功']);
		}

		return json(['code' => -1, 'msg' => '修改失败']);
	}

	    /**
     * 用户登录
     * @return mixed|Json
     */
    public function userLogin(Request $request): Response
    {
        $param = $request->post(['name','password']);

        $result = HttpHelper::withHost()->post('/v1/user/login_api', $param);

		if(!HttpHelper::ok()) {
			return json(['code'=>-1,'msg'=>$result->getLastMessage()]);
		}
        return json($result->toJson());
    }

}
