<?php
declare (strict_types = 1);

namespace app\common\controller;

use think\Controller;
use think\App;
use think\Response;
use think\exception\ValidateException;
use think\Validate;
use think\exception\HttpResponseException;
use think\facade\Session;
use think\facade\Cache;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use taoser\think\Auth;
use taoler\com\Files;
use taoler\com\Api;

/**
 * 控制器基础类
 */
abstract class AdminController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

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
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

	//获取层级
	protected function getCyl()
	{
		$sys = $this->getSystem();
		$url = $sys['base_url'].'?u='.$sys['domain'];
		$cy = Api::urlGet($url);
		if($cy && $cy->code == 0){
            $cylevel = $cy->level;
        } else {
			$cylevel = 0;
		}
		Cache::set('cylevel',$cylevel,3600);
		return Cache::get('cylevel');
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
    protected function getMenus()
    {
        $menu     = [];
        $auth_rule_list = Db::name('auth_rule')->where(['status'=> 1])->order(['sort' => 'ASC', 'id' => 'ASC'])->select();
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
	public function clearData(){
        $dir = app()->getRootPath().'runtime/admin/temp';
        $cache = app()->getRootPath().'runtime/cache';
        if(is_dir($cache)){
            Files::delDirs($cache);
        }
        if(Files::delDirs($dir) ){
            return json(['code'=>0,'msg'=>'清除成功']);
        }
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
		$sysUrl = $this->getSystem();
		$domain = $this->getHttpUrl($sysUrl['domain']);
		$syscy = $this->getCyl();
		View::assign(['domain'=>$domain,'insurl'=>$sysUrl['domain'],'syscy'=>$syscy]);
	}


}