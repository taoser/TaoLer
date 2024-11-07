<?php

declare(strict_types=1);

namespace taoser;

use think\App;
use think\helper\Str;
use think\facade\Config;
use think\facade\View;
use taoler\com\Files;
use think\facade\Cache;
use think\facade\Db;

abstract class Addons
{
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
    protected $addon_config;
    // 插件信息
    protected $addon_info;
    // 预先加载的标签库
    protected $taglib_pre_load = '';

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
        $this->addon_path = $app->addons->getAddonsPath() . $this->name . DIRECTORY_SEPARATOR;
        $this->addon_config = "addon_{$this->name}_config";
        $this->addon_info = "addon_{$this->name}_info";
        // $this->taglib_pre_load = $this->getTagLib();
        // $this->view = clone View::engine('Taoler');
        $this->view = clone View::engine('Think');
        $this->view->config([
            'strip_space'   => true, // 去除空格和换行
            'view_path' => $this->addon_path . 'view' . DIRECTORY_SEPARATOR,
            // 'taglib_pre_load'   => $this->taglib_pre_load
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
        // addons 插件视图此处必须加路径前缀/
        return $this->view->fetch('/' . $template, $vars);
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

    protected function getTagLib() {
        return Cache::remember('addon_taglib', function(){
            $tagsArr = []; 
            //获取插件下标签 addons/taglib文件
            $localAddons = Files::getDirName('../addons/');
            foreach($localAddons as $v) {
                $dir = root_path() . 'addons'. DIRECTORY_SEPARATOR . $v . DIRECTORY_SEPARATOR .'taglib';
                if(!file_exists($dir)) continue;
                $addons_taglib = Files::getAllFile($dir);
                foreach ($addons_taglib as $a) {
                    $tagsArr[] = str_replace('/','\\',strstr(strstr($a, 'addons'), '.php', true));
                }
            }
            return implode(',', $tagsArr);
        });
    }

    /**
     * 插件基础信息
     * @return array
     */
    final public function getInfo()
    {
        $info = Config::get($this->addon_info, []);
        if ($info) {
            return $info;
        }

        // 文件属性
        $info = $this->info ?? [];
        // 文件配置
        $info_file = $this->addon_path . 'info.ini';
        if (is_file($info_file)) {
            $_info = parse_ini_file($info_file, true, INI_SCANNER_TYPED) ?: [];
            $_info['url'] = addons_url();
            $info = array_merge($_info, $info);
        }
        Config::set($info, $this->addon_info);

        return isset($info) ? $info : [];
    }

    /**
     * 获取配置信息
     * @param bool $type 是否获取完整配置
     * @return array|mixed
     */
    final public function getConfig($type = false)
    {
        $config = Config::get($this->addon_config, []);
        if ($config) {
            return $config;
        }
        $config_file = $this->addon_path . 'config.php';
        if (is_file($config_file)) {
            $temp_arr = (array)include $config_file;
            if ($type) {
                return $temp_arr;
            }
            foreach ($temp_arr as $key => $value) {
                $config[$key] = $value['value'];
            }
            unset($temp_arr);
        }
        Config::set($config, $this->addon_config);

        return $config;
    }
	
	   /**
     * 设置插件信息数据
     * @param $name
     * @param array $value
     * @return array
     */
    final public function setInfo($name = '', $value = [])
    {
        if(empty($name)) {
            $name = $this->getName();
        }
        $info = $this->getInfo($name);
        $info = array_merge($info, $value);
        Config::set($info,$name);
        return $info;
    }

    //必须实现安装
    abstract public function install();

    //必须卸载插件方法
    abstract public function uninstall();

    // 写入管理位
    protected function insert(array $hooks = []) {

        if(!empty($hooks)) {
            foreach($hooks as $v) {
                $res = Db::name('addon_hook')->where([
                    'hook_name' => $v['hook_name'],
                    'hook_type' => $v['hook_type']
                ])->find();

                if(is_null($res)) {
                    Db::name('addon_hook')->save($hooks);
                }
            }
        }
    }

    // 移除管理位
    protected function remove(array $hooks = []) {

        if(!empty($hooks)) {
            foreach($hooks as $v) {
                $res = Db::name('addon_hook')->where([
                    'hook_name' => $v['hook_name'],
                    'hook_type' => $v['hook_type']
                ])->find();

                if(!is_null($res)) {
                    Db::name('addon_hook')->delete($res['id']);
                }
            }
        }
    }

}
