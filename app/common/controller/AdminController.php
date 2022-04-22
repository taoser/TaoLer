<?php
declare (strict_types = 1);

namespace app\common\controller;

use think\facade\Session;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use taoser\think\Auth;
use taoler\com\Files;
use think\facade\Lang;

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

        $auth_rule_list = Db::name('auth_rule')->where('delete_time',0)->where(['status'=> 1,'ishidden'=>1])->order(['sort' => 'asc'])->select();
        //var_export($auth_rule_list);

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

        $menu = !empty($menu) ? array2tree($menu) : [];
        View::assign('menu', $menu);
    }
	
	/**
     * 获取角色菜单
     */
    protected function getMenus($type)
    {
        $menu     = [];
        $auth_rule_list = Db::name('auth_rule')->where('delete_time',0)->where(['status'=> 1])->where('type',$type)->order(['sort' => 'ASC', 'id' => 'ASC'])->select();
        //var_export($auth_rule_list);

        foreach ($auth_rule_list as $value) {
                $menu[] = $value;  
        }
        $menus = !empty($menu) ? array2tree($menu) : [];
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
	
	//显示网站设置
    protected function getSystem()
    {
        //1.系统配置信息
		return Db::name('system')->cache('system',3600)->find(1);
       
    }
	
	//域名协议转换 把数据库中的带HTTP或不带协议的域名转换为当前协议的域名前缀
	protected function getHttpUrl($url)
	{
		//域名转换为无http协议
        $www = stripos($url,'://') ? substr(stristr($url,'://'),3) : $url;
		$htpw = Request::scheme().'://'. $www;
		return  $htpw;
	}
	
	//得到当前系统安装前台域名
	protected function getIndexUrl()
	{
		$sys = $this->getSystem();
		$domain = $this->getHttpUrl($sys['domain']);
		$syscy = $sys['clevel'] ? Lang::get('Authorized') : Lang::get('Free version');
        $runTime = $this->getRunTime();
		View::assign(['domain'=>$domain,'insurl'=>$sys['domain'],'syscy'=>$syscy,'clevel'=>$sys['clevel'],'runTime'=>$runTime]);
        return $domain;
	}

	protected function getRunTime()
    {
        //运行时间
        $now = time();
        $sys = $this->getSystem();
        $count = $now-$sys['create_time'];
        $days = floor($count/86400);
        $hos = floor(($count%86400)/3600);
        $mins = floor(($count%3600)/60);
        $years = floor($days/365);
        if($years >= 1){
            $days = floor($days%365);
        }
        $runTime = $years ? "{$years}年{$days}天{$hos}时{$mins}分" : "{$days}天{$hos}时{$mins}分";
        return $runTime;
    }

    /**
     * 获取文章链接地址
     *
     * @param integer $aid
     * @return string
     */
    protected function getRouteUrl(int $aid) : string
    {
        $indexUrl = $this->getIndexUrl();
        $artUrl = (string) url('detail_id', ['id' => $aid]);

        // 判断是否开启绑定
        //$domain_bind = array_key_exists('domain_bind',config('app'));

        // 判断index应用是否绑定域名
        $bind_index = array_search('index',config('app.domain_bind'));
        // 判断admin应用是否绑定域名
        $bind_admin = array_search('admin',config('app.domain_bind'));

        // 判断index应用是否域名映射
        $map_index = array_search('index',config('app.app_map'));
        // 判断admin应用是否域名映射
        $map_admin = array_search('admin',config('app.app_map'));

        $index = $map_index ? $map_index : 'index'; // index应用名
        $admin = $map_admin ? $map_admin : 'admin'; // admin应用名

        if($bind_index) {
            // index绑定域名
            $url = $indexUrl . str_replace($admin.'/','',$artUrl);
        } else { // index未绑定域名
            // admin绑定域名
            if($bind_admin) {
                $url =  $indexUrl .'/' . $index . $artUrl;
            } else {
                $url =  $indexUrl . str_replace($admin,$index,$artUrl);
            }
            
        }

        return $url;
    }


}