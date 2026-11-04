<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\system\AuthRule.php
 * @Description: AuthRule
 * @LastEditTime: 2023-03-14 16:45:34
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\system;

use think\Request;
use think\Response;
use think\facade\Db;
use think\facade\View;
use think\facade\Lang;
use app\entity\AuthRule as AuthRuleEntity;
use app\admin\controller\AdminBaseController;

class AuthRule extends AdminBaseController
{
	/**
	 * 权限模型
	 * @var AuthRuleEntity
	 */
	protected $entity;

    //
    public function initialize()
    {
		parent::initialize();

        $this->entity = new AuthRuleEntity();
    }

    /**
	 * 浏览菜单列表
	 */
	public function index()
	{
		return View::fetch();	
	}

	// 权限树列表 + 2026.6.23
	public function getRuleTreeList()
	{
		$data = $this->entity->getRuleTree();

		return json(['code' => 0,'msg' => 'ok','data' => $data]);
	}

	/**
	 * 权限添加/编辑
	 */
	public function form(Request $request): Response | string
	{
		if(!$request->isPost()) {
			return View::fetch();
		}

		$data = $request->post(['id/d','pid/d','title','name','icon','sort/d','type/d']);
		$this->entity->saveData($data);
		
		return json(['code' => 0,'msg' => 'ok']);
	}

	/**
	 * 删除权限
	 * 
	 * @return Response
	 */
	public function delete(Request $request): Response
	{	
		$id = $request->delete('id/d');

		$this->entity->del($id);
		
		return json(['code'=>0,'msg'=>'删除成功']);
	}

    /**
     * 无限极权限树
     * @return response
     */
	public function ruleTree(Request $request): Response
	{
		$data = $this->entity->getRuleTree();

		return json([
			'code'	=> 0,
			'msg'	=>'ok',
			'count'	=>count($data),
			'data'	=> [
				// 构造一个顶级菜单pid=0的数组。把权限放入顶级菜单下子权限中
				[
					'id'        => 0,
					'pid'       => 0,
					'title'     => Lang::get('top'),
					'children'  => $data,
				]
			]
		]);
	}

	
	

	/**
	 * 获取权限详情
	 * 
	 * @return Response
	 */
	public function getInfo(Request $request): Response
	{
		$id = $request->get('id/d');
		$rules = $this->entity->find($id);
		
		return json(['code' => 0, 'msg' => 'ok', 'data' => $rules]);
	}


    /**
     * 权限开关
     * @return Response
     */
	public function check(Request $request) : Response
	{
		$data = $request->post(['id/d','status/d']);
		$this->entity->setStatus($data);

		return json(['code'=>0,'msg'=>'审核成功']);
	}
	
	//排序
	public function sort(Request $request) : Response
	{
		$data = $request->post();
		$rules = Db::name('auth_rule')->save($data);
		if($rules){
			return json(['code'=>0,'msg'=>'排序成功']);
		}
		return json(['code'=>-1,'msg'=>'排序失败']);
	}
	
}
