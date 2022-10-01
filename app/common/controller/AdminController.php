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

namespace app\common\controller;

use think\facade\Session;
use think\facade\View;
use think\facade\Db;
use taoser\think\Auth;
use taoler\com\Files;

/**
 * 控制器基础类
 */
class AdminController extends \app\BaseController
{
    /**
     * 初始化菜单
     */
    protected function initialize()
    {
		//权限auth检查
        $this->aid = Session::get('admin_id');
		//$this->checkAuth();
		$this->getMenu();
		//系统配置
		$this->getIndexUrl();
	}

    /**
     * 获取侧边栏菜单
     */
    protected function getMenu()
    {
        $menu     = [];
        $admin_id = $this->aid;
        $auth     = new Auth();

        $auth_rule_list = Db::name('auth_rule')->where(['delete_time'=> 0,'status'=> 1,'ismenu'=>1])->select();

        foreach ($auth_rule_list as $value) {
            if ($auth->check($value['name'], $admin_id) || $admin_id == 1) {
                // 查询是否设置映射
                // $map = array_search('admin',config('app.app_map'));
                // //dump($map,$value);
                // //stripos($value);
                // if($map){
                //     $menu[] = strtr($value,'admin',$map);
                // } else {
                //     $menu[] = $value;
                // }
                //dump($menu);
                $menu[] = $value;
            }
        }

        $menu = !empty($menu) ? getTree($menu) : [];
        View::assign('menu', $menu);
    }
	
	/**
     * 获取角色菜单
     * $type 1 admin后端权限,2 index前端权限
     */
    protected function getMenus($type)
    {
        $menu     = [];
        $auth_rule_list = Db::name('auth_rule')->where(['delete_time'=> 0, 'status'=> 1,'type'=> $type])->select();
        //var_export($auth_rule_list);

        foreach ($auth_rule_list as $value) {
                $menu[] = $value;  
        }
        $menus = !empty($menu) ? getTree($menu) : [];
		//$menu2 = getTree($menu);
		return $menus;
        //return View::assign('menus', $menus);
    }
	
	//清除缓存Cache
	public function clearSysCache()
    {
        //清理缓存
		$atemp = str_replace('\\',"/",app()->getRootPath().'runtime/admin/temp/');
		$itemp = str_replace('\\',"/",app()->getRootPath().'runtime/index/temp/');
		$cache = str_replace('\\',"/",app()->getRootPath().'runtime/cache/');
		Files::delDirAndFile($atemp);
		Files::delDirAndFile($itemp);
        Files::delDirAndFile($cache);
		return true;
    }
	
	

}