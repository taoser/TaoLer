<?php
/**
 * @Program: TaoLer 2023/3/11
 * @FilePath: app\admin\controller\account\User.php
 * @Description: Account用户管理
 * @LastEditTime: 2023-03-11 10:20:47
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */
declare(strict_types=1);

namespace app\admin\controller\account;

use app\admin\controller\AdminBaseController;
use Exception;
use think\Request;
use think\Response;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Config;
use app\facade\Article;
use app\facade\Comment;
use app\entity\User as UserEntity;
use app\common\helper\Uploads;

use app\common\facade\HttpHelper;

class User extends AdminBaseController
{
	/**
	 * 用户模型	
	 * @var UserEntity
	 * @access private
	 */
	private $entity = null;

	public function initialize()
	{
		parent::initialize();

		$this->entity = new UserEntity();
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
		$param = $request->get(['id/d', 'account', 'sex', 'status']);

		$result = $this->entity->getList($param, $page, $limit);

		return json(['code' => 0, 'msg' => 'ok', ...$result]);
		
	}
	
	//添加用户
	public function add(Request $request): Response | string
	{
		if(!$request->isPost()){
			return View::fetch();
		}
		$data = $request->post(['name','email','avatar','password','phone','sex']);
		$this->entity->saveData($data);
	
		return json(['code'=>0,'msg'=>'添加成功']);
	}
	
	/**
	 * 编辑用户
	 * 
	 */
	public function edit(Request $request): Response | string
	{
		if(!$request->isPost()){
			$id = $request->get('id/d');
			$user = $this->entity::find($id);

			View::assign('user', $user);
			return View::fetch();
		}
		
		$data = $request->post(['id/d','name','email','avatar','password','phone','sex']);
		$this->entity->saveData($data);
		return json(['code'=>0,'msg'=>'编辑成功']);

	}

	/**
	 * 删除用户
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function delete(Request $request): Response
	{
		$id = $request->get('id');
		$ids = explode(',', $id);
		
		$result = $this->entity::destroy($ids);
		
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
		$data = $request->post(['id/d','status/d']);
		$this->entity->updataStatus($data);

		return json(['code' => 0, 'msg' => 'ok']);
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
