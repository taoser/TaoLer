<?php
declare (strict_types = 1);

namespace app\common\controller;

use think\App;
use think\Response;
use think\facade\View;
use think\facade\Db;
use think\exception\ValidateException;
use think\exception\HttpResponseException;
use think\facade\Session;
use think\facade\Cache;

/**
 * 控制器基础类
 */
abstract class BaseController
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
	 * 用户id
	 * @var int
	 */
	protected $uid;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
		$this->uid = Session::get('user_id');

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
		//系统配置
		$this->showSystem();
        //显示分类导航
        $this->showNav();
		$this->showUser();
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

    /**
     * 操作错误跳转
     * @param  mixed   $msg 提示信息
     * @param  string  $url 跳转的URL地址
     * @param  mixed   $data 返回的数据
     * @param  integer $wait 跳转等待时间
     * @param  array   $header 发送的Header信息
     * @return void
     */
    protected function error($msg = '', string $url = null, $data = '', int $wait = 3, array $header = []): Response
    {
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url);
        }

        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';
        if ('html' == strtolower($type)) {
            $type = 'jump';
        }

        $response = Response::create($result, $type)->header($header)->options(['jump_template' => app('config')->get('app.dispatch_error_tmpl')]);

        throw new HttpResponseException($response);
    }

    /**
     * 返回封装后的API数据到客户端
     * @param  mixed   $data 要返回的数据
     * @param  integer $code 返回的code
     * @param  mixed   $msg 提示信息
     * @param  string  $type 返回数据格式
     * @param  array   $header 发送的Header信息
     * @return Response
     */
    protected function result($data, int $code = 0, $msg = '', string $type = '', array $header = []): Response
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data,
        ];

        $type     = $type ?: 'json';
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * 操作成功跳转
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', string $url = null, $data = '', int $wait = 3, array $header = []): Response
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url);
        }

        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';
        // 把跳转模板的渲染下沉，这样在 response_send 行为里通过getData()获得的数据是一致性的格式
        if ('html' == strtolower($type)) {
            $type = 'jump';
        }

        $response = Response::create($result, $type)->header($header)->options(['jump_template' => app('config')->get('app.dispatch_success_tmpl')]);

        throw new HttpResponseException($response);
    }

	
	//判断是否已登录？
	protected function isLogged()
	{
		if(Session::has('user_id')){
			$this->success('您已登录','/index/index/index');
		}
	}

    //判断是否需要登录？
    protected function isLogin()
    {
        if(!Session::has('user_id')){
            $this->error('请登录','/index/user/login');
        }
    }
	
/*	 //判断密码找回是否已进行了邮件发送？
    protected function isMailed()
    {
        if(Cache::get('repass') != 1){
            $this->error('错误请求，请正确操作！','/index/user/forget');
        }
    }*/

//    显示导航
    protected function showNav()
    {
        //1.查询分类表获取所有分类
		$cateList = Db::name('cate')->where(['status'=>1,'delete_time'=>0])->order('sort','asc')->cache('catename',3600)->select();
		
        //2.将catelist变量赋给模板 公共模板nav.html
        View::assign('cateList',$cateList);

    }
	
	//显示当前登录用户
    protected function showUser()
    {
		$id = $this->uid;
		$user = Cache::get('user'.$id);
		if(!$user){
			//1.查询用户
			$user = Db::name('user')->field('id,name,nickname,user_img,sex,area_id,auth,city,email,sign,point,vip,create_time')->find($id);
			Cache::tag('user')->set('user'.$id,$user,600);
		}
        
		//2.将User变量赋给模板 公共模板nav.html
		View::assign('user',$user);
    }
	
	 //显示网站设置
    protected function showSystem()
    {
        //1.查询分类表获取所有分类
		$sysInfo = Db::name('system')->cache('system',3600)->find(1);
		//头部链接
		$head_links = Cache::get('headlinks');
		if(!$head_links){
			$head_links = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>8])->whereTime('slid_over','>=',time())->field('slid_name,slid_img,slid_href')->select();
			Cache::set('headlinks',$head_links,3600);
		}
		//页脚链接
		$foot_links = Cache::get('footlinks');
		if(!$foot_links){
			$foot_links = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>9])->whereTime('slid_over','>=',time())->field('slid_name,slid_href')->select();
			Cache::set('footlinks',$foot_links,3600);
		}
        View::assign(['sysInfo'=>$sysInfo,'headlinks'=>$head_links,'footlinks'=>$foot_links]);
    }

}
