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
use taoser\think\Auth;
use taoler\com\Files;

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


	//权限检查
	/**
     * 权限检查
     * @return bool
     */
/*	 
    protected function checkAuth()
    {
        if (!Session::has('admin_id')) {
			echo '请去登陆';
            return redirect('/admin/login/index');
			 //$this->error('请登录');
			 //return ['code'=>0,'url'=>'/user/login'];
        }

        $app = app('http')->getName();
        $controller = $this->request->controller();
        $action     = $this->request->action();

        // 排除权限
        $not_check = ['admin/Index/index','admin/Index/home','admin/Set/info','admin/Set/password'];

        if (!in_array($app . '/' . $controller . '/' . $action, $not_check)) {
            $auth     = new Auth();
            $admin_id = Session::get('admin_id');

            if (!$auth->check($app . '/' . $controller . '/' . $action, $admin_id) && $admin_id != 1) {
                //$this->error('没有权限');
				echo '<script>alert("没有权限");location.back()</script>';
				//return redirect('admin/user/login');
            }
        }
    }
*/	

	
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
	
	//判断是否已登录？
	protected function isLogged()
	{
		if(Session::has('admin_id')){
			return redirect('admin/index/index');
		}
	}

    //判断是否需要登录？
    protected function isLogin()
    {
        if(!Session::has('admin_id')){
			return redirect('/admin/login/index');
        }
    }

	//显示当前登录管理员
    protected function showAdmin()
    {
		$id = Session::get('admin_id');

        //1.查询管理用户
		$adminHead = Admin::find($id);
	
        //2.将User变量赋给模板 公共模板nav.html
        View::assign('adminHead',$adminHead);
    }
	
	/**创建目录
	* This function creates recursive directories if it doesn't already exist
	*
	* @param String  The path that should be created
	* 
	* @return  void
	*/
	protected function create_dir($path)
	{
	  if (!is_dir($path))
	  {
		$directory_path = "";
		$directories = explode("/",$path);
		array_pop($directories);
	   
		foreach($directories as $directory)
		{
		  $directory_path .= $directory."/";
		  if (!is_dir($directory_path))
		  {
			mkdir($directory_path);
			chmod($directory_path, 0777);
		  }
		}
	  }
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

}