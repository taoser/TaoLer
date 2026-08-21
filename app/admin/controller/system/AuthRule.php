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
	protected $model;

    //
    public function initialize()
    {
		parent::initialize();

        $this->model = new AuthRuleEntity();
    }

    /**
	 * 浏览菜单列表
	 */
	public function index()
	{
		return View::fetch();	
	}

	// 可能要废弃 ??? 2026.6.23
	public function list()
	{
		return $this->model->getAuthRuleArray();
	}

	//添加权限
	public function add(Request $request): Response | string
	{
		if(!$request->isPost()){
			return View::fetch();
		}
		$data = $request->post(['pid/d','title','name','icon','sort/d','ismenu/d']);

		$res = $this->model->add($data);
		if($res){
			return json(['code' => 0,'msg' => 'ok']);
		}

	}
	
	//权限编辑
	public function edit(Request $request): Response | string
	{
		if(!$request->isPost()){
			return View::fetch();
		}

		$data = $request->post(['id/d','pid/d','title','name','icon','sort/d','ismenu/d']);
	
		$res = $this->model->edit($data);
		if($res){
			return json(['code' => 0,'msg' => 'ok']);
		}

	}

	// 权限树列表 + 2026.6.23
	public function getRuleTreeList()
	{
		$authRules = Db::name('auth_rule')
		->field('id,pid,title,name,icon,status,ismenu,sort,create_time')
		->order('sort','asc')
		->select()
		->toArray();

		if(empty($authRules)) {
			return json(['code' => 1, 'msg' => 'no data']);
		}

		foreach($authRules as $key => $value){
			// $authRules[$key]['title'] = Lang::get($value['title']);
			$authRules[$key]['icon'] = empty($value['icon']) ? '' : 'layui-icon ' . $value['icon'];
		}

		$data = build_tree($authRules);

		return json(['code' => 0,'msg' => 'ok','data' => $data]);
	}

    /**
     * 无限极权限树
     * @return response
     */
	public function ruleTree(Request $request): Response
		{
		$data = $this->getRoleMenu(1);

		$count = count($data);
		$tree = [];			
		if($count){
			$tree = ['code'=>0, 'msg'=>'ok','count'=>$count];
			
			//构造一个顶级菜单pid=0的数组。把权限放入顶级菜单下子权限中
			$tree['data'][] = ['id'=>0, 'title'=>'顶级', 'pid'=>0, 'children'=>$data];
		}

		return json($tree);
	}

	
	/**
	 * 删除权限
	 * 
	 * @return Response
	 */
	public function delete(Request $request): Response
	{	
		$id = $request->get('id/d');
		$result = $this->model->delete($id);
		
		if($result) {
			return json(['code'=>0,'msg'=>'删除成功']);
		}
	}

	public function getInfo(Request $request): Response
	{
		$id = $request->get('id/d');
		$rules = $this->model->find($id);
		return json(['code'=>0,'msg'=>'ok','data'=>$rules]);
	}


    /**
     * 权限开关
     * @return Response
     */
	public function check(Request $request) : Response
	{
		$data = $request->post(['id/d','status/d']);
		if($data['id'] == 1 || $data['id'] == 31) {
			return json(['code' => -1, 'msg' => '不能关闭重要权限！']);
		}

		//获取状态
		$res = Db::name('auth_rule')->save($data);
		if($res){
			return json(['code'=>0,'msg'=> $data['status'] == 1 ? '权限开启':'权限禁用']);
		}
		return json(['code'=>-1,'msg'=>'审核出错']);
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
