<?php
declare (strict_types = 1);

namespace app\common\controller;

use think\Controller;
use think\App;
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
    // 初始化
    protected function initialize()
    {
		//权限auth检查
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
        $admin_id = Session::get('admin_id');
        $auth     = new Auth();

        $auth_rule_list = Db::name('auth_rule')->where(['status'=> 1,'ishidden'=>1])->order(['sort' => 'asc'])->select();
        //var_export($auth_rule_list);

        foreach ($auth_rule_list as $value) {
            if ($auth->check($value['name'], $admin_id) || $admin_id == 1) {
                $menu[] = $value;
            }
        }

        $menu = !empty($menu) ? array2tree($menu) : [];
        return View::assign('menu', $menu);
    }
	
	/**
     * 获取角色菜单
     */
    protected function getMenus($type)
    {
        $menu     = [];
        $auth_rule_list = Db::name('auth_rule')->where(['status'=> 1])->where('type',$type)->order(['sort' => 'ASC', 'id' => 'ASC'])->select();
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


}