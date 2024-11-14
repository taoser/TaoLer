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

namespace app\controller\admin;

use think\facade\Session;
use think\facade\View;
use think\facade\Db;
use taoser\think\Auth;
use taoler\com\Files;
use think\facade\Lang;
use think\facade\Cookie;
use think\facade\Config;
use think\facade\Cache;

/**
 * 控制器基础类
 */
class AdminBaseController extends \app\BaseController
{
    // 中间件
    protected $middleware = [\app\middleware\AdminAuth::class];

    // 管理员id
    protected $aid;

    /**
     * 初始化菜单
     */
    protected function initialize()
    {
		//权限auth检查
        $this->aid = $this->request->aid;
		//系统配置
        $sys = $this->getSystem();
        $syscy = $sys['clevel'] ? Lang::get('Authorized') : Lang::get('Free version');
        $runTime = $this->getRunTime();

        View::assign([
            // 'domain' => $this->getDomain(),
            'domain' => 'http://www.tp6.com',
            'insurl'=>$sys['domain'],
            'syscy'=>$syscy,
            'clevel'=>$sys['clevel'],
            'runTime'=>$runTime
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
        Cache::clear(); 
		$atemp = str_replace('\\',"/",app()->getRootPath().'runtime/admin/temp/');
		$itemp = str_replace('\\',"/",app()->getRootPath().'runtime/index/temp/');
		$cache = str_replace('\\',"/",app()->getRootPath().'runtime/cache/');
		Files::delDirAndFile($atemp);
		Files::delDirAndFile($itemp);
        Files::delDirAndFile($cache);
		return true;
    }

    /**
     * 把后台管理文章或帖子的路由转换为实际应用的路由
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

        return $this->appConver($appName, $articleUrl);
        
    }

    //后台管理用户的路由转换为前台用户中心的路由
    //直接登录用户中心
    protected function getUserHome($uid) {
        $user = Db::name('user')->field('id,name')->find($uid);
		$salt = Config::get('taoler.salt');
		$auth = md5($user['name'].$salt).":".$user['id'];
    	Cookie::set('auth',$auth,604800);
        $url = (string) url('user/index');
        return $this->appConver('index', $url);
    }


    /**
     * APP应用转换,在后台admin应用转换为在其它app应用的路径
     * /admin/user/info转换为 /index/user/info
     * @param string $appName 要转换为哪个应用
     * @param string $url 路由地址
     * @return string
     */
    public function appConver(string $appName, string $url) :string
    {
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
                return $this->getDomain() . $url;
            }
            // 2.应用进行了映射
            if($app_map){
                return $this->getDomain() . '/' . $appName . $url;
            }
            // 3.应用未绑定域名也未进行映射
            return $this->getDomain() . '/' . $appName . $url;
        }

        //2.admin进行了映射
        if($map_admin) {
            // 1.应用绑定了域名
            if($app_bind) {
                return $this->getDomain() . str_replace($map_admin, '', $url);;
            }
            // 2.应用进行了映射
            if($app_map){
                return $this->getDomain() . str_replace($map_admin, $app_map, $url);
            }
            // 3.应用未绑定域名也未进行映射
            return  $this->getDomain() . str_replace($map_admin, $appName, $url);
        }
        //3.admin未绑定域名也未映射
        // 1.应用绑定了域名
        if($app_bind) {
            return $this->getDomain() . $url;
        }
        // 2.应用进行了映射
        if($app_map){
            return $this->getDomain() . str_replace('admin', $app_map, $url);
        }
        return str_replace('admin', $appName, $url);
    }
	

}