<?php
namespace app\admin\controller\system;

use app\admin\controller\AdminBaseController;
use think\Request;
use think\Response;
use think\facade\View;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Cookie;
use think\facade\Session;
use app\common\helper\FileHelper;
use app\common\helper\PasswordHash;
use app\entity\Admin as AdminEntity;


class Admin extends AdminBaseController
{
	/**
     * 管理员模型
     * @var AdminEntity
     */
    protected $entity = null;

    public function initialize()
    {
        parent::initialize();

        $this->entity = new AdminEntity();
    }

	/**
	 * 管理员列表页
	 *
	 * @param Request $request
	 * @return string | Response
	 */
	public function index(Request $request): string | Response
	{		
		return View::fetch();
	}

    /**
     * 管理员列表
     * @param Request $request
     * @return Response
     */
	public function list(Request $request): Response
	{
		$account = $request->get('account');
		$result = $this->entity->getList($account);
		return json(['code' =>0, 'msg' => '查询成功', 'count' => $result['count'], 'data' => $result['data']]);
	}
	
	/**
	 * 添加管理员
	 *
	 * @param Request $request
	 * @return Response | string
	 */
	public function add(Request $request): Response | string
	{
		if(!$request->isPost()){
			return View::fetch();
		}

		$data = $request->post(['username','email','password','mobile']);
		$this->entity->add($data);
		
		return json(['code' => 0, 'msg' => '添加成功']);
	}
	
	/**
	 * 编辑管理员
	 *
	 * @param Request $request
	 * @return Response | string
	 */
	public function edit(Request $request): Response | string
	{
		if(!$request->isPost()){
			return View::fetch();
		}

		$data = $request->post(['id/d', 'email', 'password', 'mobile','remarks']);
		$this->entity->edit($data);
		return json(['code' => 0, 'msg' => '编辑成功']);
	}
	
	// 删除管理员
	public function delete(Request $request): Response
	{
		$id = $request->get('id');
		$user = $this->entity->find($id);

		$result = $user->delete();
		if($result) {
			return json(['code' => 0, 'msg' => '删除成功']);
		}
		return json(['code' => 400, 'msg' => '删除失败']);
	}

	/**
	 * 审核管理员
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function check(Request $request): Response
	{
		$data = $request->post(['id', 'status']);
		$this->entity->audit($data);
		return json(['code' => 0, 'msg' => 'ok', 'icon' => 5]);
	}

	// 基本资料浏览
	public function info(Request $request)
	{
		$id = $request->get('id');
		$admin = $this->entity->field('id,username,mobile,email,remarks')->find($id);
		return json(['code' => 0, 'msg' => 'ok', 'data' => $admin]);
    }

	// 管理员资料更新
	public function setInfo(Request $request): Response | string
    {
		$id = $request->aid;
		$admin = $this->entity->field('id,username,mobile,email,remarks')->find($id);

		if(!$request->isPost()) {
			return View::fetch('info_edit',['admin' => $admin]);
		}

		$data = $request->post(['username','mobile','email','remarks']);
		$res = $admin->save($data);
		if(!$res){
			return json(['code' => 400, 'msg' => '更新失败']);
		}
		return json(['code' => 0, 'msg' => '更新成功']);
    }

    // 修改密码
	public function newPassword(Request $request): Response
	{
		if(!$request->isPost()) {
			return view('new_pass');
		}
		$data = $request->post(['currentPassword','password','confirm_password']);
		$data['id'] = $request->aid;
		$this->entity->setNewPassword($data);
		return json(['code' => 0, 'msg' => '修改成功']);
	}

    /**
     * 清除缓存Cache
     * @return Response
     */
	public function clearCache(Request $request): Response
    {
		Cache::clear();
		// 清除临时文件
		$temp = str_replace('\\',"/", runtime_path().'temp/');
		$res = FileHelper::emptyDir($temp);
		if(!$res){
			return json(['code' => -1, 'msg' => '清除缓存失败']);
		}
		return json(['code' => 0,'msg'=>'清除缓存成功']);
    }
	
	// 退出登陆
	public function logout(Request $request): Response
	{
		// 清空Cookie
		Cookie::delete('admin_token');
		// 清空Session
		Session::clear();
		return json(['code' => 0, 'msg'=>'退出成功' ]);
	}

	public function getPermission(Request $request): Response
	{
		$admin = $this->entity->find($request->aid);

		$permission = $admin->getPermission();

		array_walk($permission, function(&$item) {
			$item = str_replace('/', '.', strtolower(trim($item)));
		});
		return json(['code' => 0, 'msg' => 'ok', 'data' => $permission]);
	
	}

	/**
	 * 获取管理员权限规则
	 *
	 * @return Response
	 */
	public function getRules(): Response
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
			$nameArr = Db::name('auth_rule')->whereIn('id', $ruleArr)->where('status',1)->where('type', 2)->column('name');
			foreach($nameArr as $v){
				$codes[] = str_replace('/', '.', strtolower(trim($v)));
			}
		}

		return json(['code' => 0, 'msg' => 'ok', 'data' => $codes]);
	}

}