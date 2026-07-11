<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-05-17 11:15:46
 * @LastEditors: TaoLer
 * @Description: 后台控制器设置
 * @FilePath: \TaoLer\app\common\controller\AdminController.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
declare (strict_types = 1);

namespace app\admin\controller;

use think\facade\Session;
use think\facade\View;
use think\facade\Db;
use taoser\think\Auth;
use think\facade\Lang;
use think\facade\Cookie;
use think\facade\Config;

/**
 * 控制器基础类
 */
class AdminBaseController extends \app\BaseController
{

    protected $aid = '';

    /**
     * 初始化菜单
     */
    protected function initialize()
    {
		//权限auth检查
        $this->aid = Session::get('admin_id');

		//系统配置
        $sys = $this->getSystem();
        $syscy = $sys['clevel'] ? Lang::get('Authorized') : Lang::get('Free version');
        $runTime = $this->getRunTime();
        
        // 用于加密的模块名称 访问路径前缀
        $moduleName = Config::get('taoler.admin_module_name');

        View::assign([
            'moduleName'    => $moduleName,
            'domain'        => $this->getDomain(),
            'insurl'        => $sys['domain'],
            'syscy'         => $syscy,
            'clevel'        => $sys['clevel'],
            'runTime'       => $runTime
        ]);
	}

    /**
     * 菜单无限极分类
     *
     * @param array $data 包含有pid的rule权限数组
     * @param integer $pId 父ID
     * @return array
     */
    public function getRuleTree(array $data, int $pId = 0): array
    {
        // 递归
        $tree = [];
        foreach ($data as $k => $v) {
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($v['pid'] == $pId) {
                $child = $this->getRuleTree($data, $v['id']);
                // 有子类
                if(!empty($child)) {
                    $v['type'] = $v['pid'] == 0 ? 0 : $v['ismenu'];
                    $v['children'] = $child;
                    $v['isParent'] = true;
                } else {
                    // 没有子菜单type=1
                    $v['type'] = 1;
                    $v['openType'] = '_iframe';
                    $v['isParent'] = false;
                }
                
                //把数组放到$tree中
                $tree[] = $v;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($data[$k]);
            }
        }
       
        return $tree;
    }

    /**
     * 获取侧边栏菜单
     */
    protected function getMenu()
    {
        $menu     = [];
        $admin_id = $this->aid;
        $auth     = new Auth();

        $auth_rule_list = Db::name('auth_rule')
        ->where(['status' => 1, 'ismenu' => 1, 'delete_time'=> 0])
        ->select();

        foreach ($auth_rule_list as $value) {
            if ($auth->check($value['name'], $admin_id) || $admin_id == 1) {
                $menu[] = $value;
            }
        }

        return !empty($menu) ? build_tree($menu) : [];
    }
	
	/**
     * 获取角色菜单
     * $type 1 admin后端权限, 2 index前端权限
     */
    protected function getRoleMenu($type)
    {
        $authRuleList = Db::name('auth_rule')
        ->field('id,pid,title,sort,level')
        ->where(['type'=> $type, 'status'=> 1, 'delete_time' => 0])
        ->order('sort','asc')
        ->select()
        ->toArray();

        if(empty($authRuleList)){
            return [];
        }

        foreach ($authRuleList as &$v) {
            $v['title'] = Lang::get($v['title']); 
        }
        unset($v);
        
        return build_tree($authRuleList);
    }
	

}