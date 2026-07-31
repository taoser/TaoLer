<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-10-15
 * Time: 15:40
 */

namespace app\admin\controller\system;

use app\admin\controller\AdminBaseController;
use app\entity\Admin as AdminEntity;
use think\Response;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Cache;
use app\common\helper\FileHelper;
use app\common\helper\PasswordHash;


class Admin extends AdminBaseController
{
	/**
     * 管理员模型
     * @var AdminEntity
     */
    protected $model = null;

    public function initialize()
    {
        parent::initialize();

        $this->model = new AdminEntity();
    }

    /**
     * 浏览管理员
     * @return string
     */
	public function index()
	{
		return View::fetch();
	}

    /**
	 * 管理员列表
	 *
	 * @return Response
	 */
	public function list():Response
	{
		$data = Request::only(['id','username','mobile','email']);
		$map = array_filter($data);

		$admins = $this->model
		->field('id,avatar,username,mobile,email,remarks,status,last_login_ip,last_login_time,create_time')
		->where($map)
		->select();

		$count = $admins->count();
		if($count){
			return json(['code' => 0, 'msg' => 'ok', 'count' => $count, 'data' => $admins]);
		}

		return json(['code' => 1,'msg' => 'no data']); 
	}

	
	//管理员审核
	public function check():Response
	{
		$data = Request::only(['id', 'status']);

        if($data['id'] == 1 && $data['status'] == -1) {
            return json(['code' => -1, 'msg' => '无法禁用超级管理员']);
        }

		//获取状态
		$res = $this->model->where('id', $data['id'])->save(['status' => $data['status']]);
		if($res){
			if($data['status']){
				return json(['code' => 0, 'msg' => '审核通过', 'icon' => 6]);
			}
			return json(['code' => 0, 'msg' => '审核取消', 'icon' => 5]);
		}

		return json(['code' => -1, 'msg' => '审核出错']);
	}
	
	//添加管理员
	public function add()
	{
		if(Request::isAjax()){
			$data = Request::only(['username','email','password','mobile']);
			$roleId = request()->get('roleId');
	
			$data['password'] = PasswordHash::make($data['password']);
			
			$admin = $this->model->save($data);
			//Db::name('auth_group_access')->insert(['uid'=>$adminId,'group_id'=>$data['auth_group_id']]);
			if($admin){
				return json(['code'=>0,'msg'=>'添加成功']);
			}

			return json(['code'=>-1,'msg'=>'添加失败']);
		}
		//$auth_group = Db::name('auth_group')->select();
		//View::assign(['auth_group'=>$auth_group]);
		return View::fetch();
	}
	
	//管理员编辑
	public function edit()
	{
		$id = Request::param('id/d');
		$admin = $this->model->find($id);
		
		if(Request::isAjax()){
			$data = Request::only(['id','email','password','mobile','roleId']);
			$result = AdminEntity::edit($data);
			//Db::name('auth_group_access')->where('uid',$data['id'])->update(['group_id'=>$data['auth_group_id']]);
			if($result){
				return json(['code'=>0,'msg'=>'编辑成功']);
			}
			return json(['code'=>-1,'msg'=>'编辑失败']);
		}
		//$auth_group = Db::name('auth_group')->select();,'auth_group'=>$auth_group
		View::assign(['admin'=>$admin]);
		return View::fetch();
	}
	
	//删除管理员
	public function delete()
	{
		$id = Request::param('id/d');
		
		$user = $this->model->find($id);
		$result = $user->delete();
		if($result){
			return json(['code'=>0,'msg'=>'删除成功']);
		}

		return json(['code'=>-1,'msg'=>'删除失败']);
	}

	//基本资料浏览
	public function info()
    {
		$admin = $this->model->find($this->aid);
		$auths = $admin->adminGroup;
		$authName = [];
		foreach($auths as $v){
            $authName[] = $v->title;
        }
        $authGroupTitle = implode('|', $authName);

		View::assign(['admin' => $admin, 'authGroupTitle' => $authGroupTitle]);
		return View::fetch();
    }

	//修改基本资料显示
	public function infoEdit()
    {
		$admin = $this->model->find($this->aid);
		$auths = $admin->adminGroup;
		$authName = [];
		foreach($auths as $v){
            $authName[] = $v->title;
        }
        $authGroupTitle = implode('|', $authName);

		View::assign(['admin'=>$admin,'authGroupTitle'=>$authGroupTitle]);
		return View::fetch();
    }

	//管理员资料更新
	public function infoSet()
    {
		$admin = $this->model->find($this->aid);
        
		$data = Request::only(['nickname','mobile','email','remarks']);
		$result = $admin->save($data);
		if($result){
			return json(['code'=>0,'msg'=>'更新成功']);
		}
		return json(['code'=>-1,'msg'=>'更新失败']);
    }

    //浏览改密码页面
    public function repass()
    {
		return View::fetch();
    }
	
    //修改密码
	public function repassSet() 
	{
		
		$data = Request::only(['oldPassword','password','repassword']);
		$data['admin_id'] = $this->aid;
		
		try{
			$result = $this->model->setpass($data);
			if($result){
				return json(['code'=>0,'msg'=>'修改密码成功']);
			}
			return json(['code'=>-1,'msg'=>'修改密码失败']);
		} catch (\Exception $e) {
			return json(['code' => -1, 'msg' => $e->getMessage()]);
		}
	}

    /**
     * 清除缓存Cache
     * @return \think\response\Json
     */
	public function clearCache()
    {
		try{
			//清理缓存
			Cache::clear();
			// 清除临时文件
			$temp = str_replace('\\',"/", runtime_path().'temp/');
			FileHelper::emptyDir($temp);

			return json(['code' => 0,'msg'=>'清除缓存成功']);

		} catch (\Exception $e) {
			return json(['code' => -1, 'msg' => $e->getMessage()]);
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

	/**
	 * 获取管理员权限规则
	 *
	 * @return Response
	 */
	public function getRules() :Response
	{
		$codes = [];
		$ruleArr = [];

		$groupIdArr = Db::name('auth_group_access')->where('uid', $this->aid)->where('status',1)->group('group_id')->column('group_id');
		
		if(!is_null($groupIdArr)) {
			$rules = Db::name('auth_group')->whereIn('id', $groupIdArr)->where('status', 1)->column('rules');
			// 遍历拆分 + 合并 + 去重 + 重置下标
			$temp = [];
			foreach ($rules as $item) {
				$temp = array_merge($temp, explode(',', $item));
			}
			$ruleArr = array_values(array_unique($temp));
		}

		if(!empty($ruleArr)){
			$nameArr = Db::name('auth_rule')->whereIn('id', $ruleArr)->where('status',1)->where('ismenu',2)->column('name');
			foreach($nameArr as $v){
				$codes[] = str_replace('/', '.', strtolower(trim($v)));
			}
		}

		return json(['code' => 0, 'msg' => 'ok', 'data' => $codes]);
	}
}