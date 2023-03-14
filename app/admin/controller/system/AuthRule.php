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

use app\common\controller\AdminController;
use think\App;
use think\facade\Request;
use think\facade\Db;
use think\facade\View;
use app\admin\model\AuthRule as AuthRuleModel;

class AuthRule extends AdminController
{

	protected $model = '';

    //
    public function __construct()
    {
        $this->model = new AuthRuleModel();
    }

    /**
	 * 浏览菜单列表
	 */
	public function index()
	{
		
		return View::fetch();	
		
	}

	public function list()
	{
		return $this->model->getAuthRuleArray();
	}
	
	/**
	 * 无限极权限树
	 *
	 * @return void
	 */
	public function ruleTree()
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
	
	//添加权限
	public function add()
	{
		if(Request::isAjax()){
			$data = Request::param();
            //层级level
            $plevel = Db::name('auth_rule')->field('level')->find($data['pid']);
			if($plevel){
				$data['level'] = $plevel['level']+1;
			} else {
				$data['level'] = 0;
			}

            return $this->model->saveRule($data);
		}

		$auth_rules = $this->model->getAuthRuleArray();
		View::assign('AuthRule',$auth_rules);
		return View::fetch();
	}
	
	//权限编辑
	public function edit()
	{
		$rule = new AuthRuleModel();
		
		if(Request::isAjax()){
			$data = Request::param(['id','pid','title','name','icon','sort','ismenu']);
			//层级level
            $ruId = $rule->find($data['pid']); //查询出上级ID
			if($ruId){
				$plevel = $ruId->level; //上级level等级
				$data['level'] = $plevel+1;	
			} else {
				$data['level'] = 0;
			}
			$zi = $this->model->where('pid',$data['id'])->select();//查询出下级
            if(!empty($zi)){
                $zi->update(['level'=>$data['level']+1]);
            }

			$rule = $this->model->find($data['id']);
            return $rule->saveRule($data);
		}
		
		$auth_rules = $this->model->getAuthRuleArray();
		$rules = $this->model->find(input('id'));

		View::assign(['AuthRule'=>$auth_rules,'rules'=>$rules]);
		return View::fetch();
	}

	
	/**
	 * 删除权限
	 *
	 * @param [type] $id
	 * @return void
	 */
	public function delete($id)
	{	
		$pids = AuthRuleModel::where('pid',$id)->select();
		if($pids)
		{
			$result = $pids->delete();
		}
		
		$rule = AuthRuleModel::find($id);
		$result = $rule->delete();
		if($result){
			$res = ['code'=>0,'msg'=>'删除成功'];
		} else {
			$res = ['code'=>-1,'msg'=>'删除失败'];
		}
	return json($res);	
	}


    /**
     * 权限开关
     * @return \think\response\Json
     */
	public function check()
	{
		$data = Request::only(['id','status']);
		if($data['id'] == 1 || $data['id'] == 31) {
			return json(['code' => -1, 'msg' => '不能关闭重要权限！']);
		}

		//获取状态
		$res = Db::name('auth_rule')->save($data);
		if($res){
			if($data['status'] == 1){
				return json(['code'=>0,'msg'=>'权限开启','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'权限禁用','icon'=>5]);
			}
			
		}else {
			return json(['code'=>-1,'msg'=>'审核出错']);
		}
	
	}
	
	//排序
	public function sort()
	{
		$data = Request::param();
		$rules = Db::name('auth_rule')->save($data);
		if($rules){
			$res = ['code'=>0,'msg'=>'排序成功'];
		}else{
			$res = ['code'=>-1,'msg'=>'排序失败'];
		} 
		return json($res);
	}
	
	
}
