<?php

declare(strict_types=1);

namespace taoser\addons;

use think\App;
use think\helper\Str;
use think\facade\Lang;
use think\facade\Config;
use think\facade\View;

class Controller
{

    /**
     * 中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * @var Model
     */
    protected $model = null;

    /**
     * 无需登录及鉴权的方法
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 需要登录无需鉴权的方法
     * @var array
     */
    protected $noNeedAuth = [];

    // app 容器
    protected $app;
    // 请求对象
    protected $request;
    // 当前插件标识
    protected $name;
    // 插件路径
    protected $addonPath;
    // 视图模型
    protected $view;
    // 插件配置
    protected $addon_config = '';
    // 插件信息
    protected $addon_info = '';


    /**
     * 插件构造函数
     * Addons constructor.
     * @param \think\App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app->request;
        $this->name = $this->getName();
        $this->addonPath = $app->addons->getAddonsPath() . $this->name . DIRECTORY_SEPARATOR;
        $this->addon_config = "addon_{$this->name}_config";
        $this->addon_info = "addon_{$this->name}_info";
        $this->view = null;
        static $middlewareCache = null;
        if ($middlewareCache === null) {
            $middlewareCache = is_file(addons_path() . 'middleware.php') ? include addons_path() . 'middleware.php' : [];
        }
        $this->middleware = $middlewareCache;

        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

    /**
     * 刷新请求对象（Workerman/Swoole 常驻进程下复用插件实例时调用）
     * @param \think\Request $request
     * @return void
     */
    public function setRequest(\think\Request $request): void
    {
        $this->request = $request;
        $this->app = \think\App::getInstance();
        $this->view = null;
    }

    /**
     * 获取插件标识
     * @return mixed|null
     */
    final protected function getName()
    {
        $class = get_class($this);
        list(, $name, ) = explode('\\', $class);
        $this->request->addon = $name;

        return $name;
    }

    /**
     * 加载模板输出
     * @param string $template
     * @param array $vars           模板文件名
     * @return false|mixed|string   模板输出变量
     * @throws \think\Exception
     */
    protected function fetch($template = '', $vars = [])
    {
        if ($this->view === null) {
            $this->initView();
        }
        return $this->view->fetch($template, $vars);
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param  string $content 模板内容
     * @param  array  $vars    模板输出变量
     * @return mixed
     */
    protected function display($content = '', $vars = [])
    {
        if ($this->view === null) {
            $this->initView();
        }
        return $this->view->display($content, $vars);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param  mixed $name  要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
     */
    protected function assign($name, $value = '')
    {
        if ($this->view === null) {
            $this->initView();
        }
        if (is_array($name)) {
            $this->view->assign($name);
        } else {
            $this->view->assign([$name => $value]);
        }

        return $this;
    }

    /**
     * 初始化模板引擎
     * @access protected
     * @param  array|string $engine 引擎参数
     * @return $this
     */
    protected function engine($engine)
    {
        if ($this->view === null) {
            $this->initView();
        }
        $this->view->engine($engine);

        return $this;
    }

    private function initView(): void
    {
        $this->view = clone View::engine('Think');
        $taglib = get_addons_taglib();
        $config = [
            'strip_space' => true,
            'view_path' => $this->addonPath . 'view' . DIRECTORY_SEPARATOR
        ];
        if ($taglib !== '') {
            $config['taglib_pre_load'] = $taglib;
        }
        $this->view->config($config);
    }
}