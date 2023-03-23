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
use think\facade\Lang;

/**
 * 控制器基础类
 */
class AdminController extends \app\BaseController
{

    protected $aid = '';

    protected $appName = '';

    /**
     * 初始化菜单
     */
    protected function initialize()
    {
		//权限auth检查
        $this->aid = Session::get('admin_id');
		//系统配置
        $sys = $this->getSystem();
        $domain = $this->getHttpUrl($sys['domain']);
        $syscy = $sys['clevel'] ? Lang::get('Authorized') : Lang::get('Free version');
        $runTime = $this->getRunTime();
        View::assign(['domain'=>$domain,'insurl'=>$sys['domain'],'syscy'=>$syscy,'clevel'=>$sys['clevel'],'runTime'=>$runTime]);
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
                    // foreach($child as $m => $n) {
                    //     $v['children'][$m] = $n;
                    //     //$v['children'][$m]['type'] = 1;
                    //     //$v['children'][$m]['openType'] = '_iframe';
                    // }
                    $v['type'] = $v['pid'] == 0 ? 0 : $v['ismenu'];
                    $v['children'] = $child;
                } else {
                    // 没有子菜单type=1
                    $v['type'] = 1;
                    $v['openType'] = '_iframe';
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

        $auth_rule_list = Db::name('auth_rule')->where(['status'=> 1, 'ismenu'=>1, 'delete_time'=> 0])->select();
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

        return !empty($menu) ? getTree($menu) : [];
    }
	
	/**
     * 获取角色菜单
     * $type 1 admin后端权限,2 index前端权限
     */
    protected function getRoleMenu($type)
    {
        $menu     = [];
        $auth_rule_list = Db::name('auth_rule')->field('id,pid,title,sort,level')->where(['delete_time'=> 0, 'status'=> 1,'type'=> $type])->select()->toArray();
        // 排序
        $cmf_arr = array_column($auth_rule_list, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $auth_rule_list);
        foreach ($auth_rule_list as $value) {
                $menu[] = [
                    'id' => $value['id'],
                    'pid' => $value['pid'],
                    'title' => Lang::get($value['title']),
                    'level' => $value['level']
                ];  
        }
        return !empty($menu) ? getTree($menu) : [];
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

    /**
     * 获取路由
     * @param int $aid
     * @param string $appName
     * @param string $ename
     * @return string|void
     */
    protected function getArticleUrl(int $aid, string $appName = 'index', string $ename = '' )
    {
        // admin管理后台 解析非admin应用路由
        //$appName = app('http')->getName();

        $articleUrl = (string) url('article_detail', ['id' => $aid]);
        // 详情动态路由，$aid, $ename
        if(config('taoler.url_rewrite.article_as') == '<ename>/'){
            $articleUrl = (string) url('article_detail', ['id' => (int) $aid, 'ename'=> $ename]);
        }

        // 判断应用是否绑定域名
        $app_bind = array_search($appName, config('app.domain_bind'));
        // 判断应用是否域名映射
        $app_map = array_search($appName, config('app.app_map'));

        // 判断admin应用是否绑定域名
        $bind_admin = array_search('admin',config('app.domain_bind'));
        // 判断admin应用是否域名映射
        $map_admin = array_search('admin',config('app.app_map'));

        //1.admin绑定了域名
        if($bind_admin) {
            // 1.应用绑定了域名
            if($app_bind) {
                return $this->getIndexUrl() . $articleUrl;
            }
            // 2.应用进行了映射
            if($app_map){
                return $this->getIndexUrl() . '/' . $appName . $articleUrl;
            }
            // 3.应用未绑定域名也未进行映射
            return $this->getIndexUrl() . '/' . $appName . $articleUrl;
        }

        //2.admin进行了映射
        if($map_admin) {
            // 1.应用绑定了域名
            if($app_bind) {
                return $this->getIndexUrl() . str_replace($map_admin, '', $articleUrl);;
            }
            // 2.应用进行了映射
            if($app_map){
                return $this->getIndexUrl() . str_replace($map_admin, $app_map, $articleUrl);
            }
            // 3.应用未绑定域名也未进行映射
            return  $this->getIndexUrl() . str_replace($map_admin, $appName, $articleUrl);
        }
        //3.admin未绑定域名也未映射
        return str_replace('admin', $appName, $articleUrl);
    }


	
	

}