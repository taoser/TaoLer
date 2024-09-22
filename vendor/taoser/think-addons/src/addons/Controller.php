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
    protected $addon_path;
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
    public function __construct(App $app = null)
    {
        $this->app = $app;
        $this->request = $app->request;
        $this->name = $this->getName();
        $this->addon_path = $app->addons->getAddonsPath() . $this->name . DIRECTORY_SEPARATOR;
        $this->addon_config = "addon_{$this->name}_config";
        $this->addon_info = "addon_{$this->name}_info";
        $this->view = View::engine('Taoler');
        $this->view->config([
            'type' => 'Taoler',
            'strip_space' => true, // 去除空格和换行
            'view_path' => $this->addon_path . 'view' . DIRECTORY_SEPARATOR
        ]);

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

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
        $this->view->engine($engine);

        return $this;
    }



}
