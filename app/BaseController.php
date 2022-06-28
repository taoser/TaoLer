<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;
use think\Response;
use think\exception\HttpResponseException;
use think\facade\Db;
use think\facade\Request;
use think\facade\Lang;
use think\facade\View;

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
    {}

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
                [$validate, $scene] = explode('.', $validate);
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
    protected function getRouteUrl(int $aid,string $ename = '') : string
    {
        $indexUrl = $this->getIndexUrl();
        if(config('taoler.url_rewrite.article_as') == '<ename>/'){
            // 分类可变路由
            $artUrl = (string) url('detail_id', ['ename'=> $ename,'id' => $aid]);
        } else {
            $artUrl = (string) url('detail_id', ['id' => $aid]);
        }
        

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
