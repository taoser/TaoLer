<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;
use think\facade\View;
use think\facade\Db;
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
	 * 登录用户uid
	 *
	 * @var int|null
	 */
	protected $uid = null;

	/**
	 * 登录用户信息
	 *
	 * @var array|object
	 */
	protected $user = [];

	protected $isLogin = false;

	protected $adminEmail;

	protected $hooks = [];

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
    protected function validate(array $data, string|array $validate, array $message = [], bool $batch = false)
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

    //显示网站设置
    protected function getSystem()
    {
        //1.系统配置信息
        return Db::name('system')->cache('system',3600)->find(1);
        
    }

    /**
     * 运行时间计算
     * @return string
     */
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

    protected function getDomain() {
        return $this->request->domain();
    }

}
