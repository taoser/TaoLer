<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\system\AuthRole.php
 * @Description: AuthRole角色控制器
 * @LastEditTime: 2023-03-14 16:43:59
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\system;

use app\admin\controller\AdminBaseController;
use think\Request;
use think\Response;
use think\facade\View;
use app\entity\AuthRole as AuthRoleEntity;

class AuthRole extends AdminBaseController
{
	/**
	 *
	 * @var AuthRoleEntity
	 */
	protected $entity = null;

	public function initialize()
    {
		parent::initialize();
        $this->entity = new AuthRoleEntity;
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
	
	/**
	 * 角色列表
	 */
	public function list(Request $request): Response
	{
		$list = $this->entity->getList();
		return json(['code' => 0,'msg'=>'ok','count' => $list['count'], 'data' => $list['data']]);		
	}

	public function info(Request $request): Response
	{
		$id = $request->get('id/d');
		$role = $this->entity->find($id);
		return json(['code' => 0, 'msg' => 'ok','data' => $role]);
	}

	public function form(Request $request): Response | string
	{
		if(!$request->isPost()){
			return View::fetch();
		}

		$data = $request->post(['id','name','remark','sort/d']);
		$this->entity->saveData($data);

		return json(['code' => 0, 'msg' => '保存成功']);
	}

	/**
	 * 角色权限
	 */
	public function rule(Request $request): Response | string
	{
		if(!$request->isPost()){
			return View::fetch();
		}

		$data = $request->post(['role_id/d','rule_ids']);
		$this->entity->saveRoleRule($data);
		
		return json(['code'=>0,'msg'=>'保存成功']);
	}

	/**
	 * 角色管理员
	 */
	public function auth(Request $request): Response | string
	{
		if(!$request->isPost()){
			return View::fetch();
		}

		$data = $request->post(['role_id/d','admin_ids/a']);
		$this->entity->saveAdminRole($data);
		return json(['code' => 0, 'msg' => '保存成功']);
	}

	/**
	 * 获取角色权限
	 */
	public function getRoleRules(Request $request): Response
	{
		$roleId = $request->get('id/d');
		$result = $this->entity->getRoleRules($roleId);
		return json(['code' => 0, 'msg' => 'ok', 'data' => $result]);
	}

	/**
	 * 获取角色管理员
	 */
	public function getAdmins(Request $request): Response
	{
		$roleId = $request->get('id/d');
		$result = $this->entity->getAdmins($roleId);

		return json(['code' => 0, 'msg' => 'ok', 'data' => $result]);
	}

	


	//角色删除
	public function delete(Request $request): Response
	{
		$id = $request->get('id/d');
		$this->entity->delete($id);
		return json(['code' => 0, 'msg' => '删除成功']);
	}
	
	//角色审核
	public function check(Request $request): Response
	{
		
	}

	
	
}
