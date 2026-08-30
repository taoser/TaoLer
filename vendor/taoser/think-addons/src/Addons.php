<?php

declare(strict_types=1);

namespace taoser;

use think\App;
use think\helper\Str;
use think\facade\Config;
use think\facade\View;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Template;

abstract class Addons
{
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
    protected $addonConfig;
    // 插件信息
    protected $addonInfo;
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
        $this->addonPath = $this->app->addons->getAddonsPath() . $this->name . DIRECTORY_SEPARATOR;
        $this->addonConfig = "addon_{$this->name}_config";
        $this->addonInfo = "addon_{$this->name}_info";
        // $this->taglib_pre_load = $this->getTagLib();
        // $this->view = clone View::engine('Taoler');
        $this->view = clone View::engine('Think');
        $this->view->config([
            'strip_space'   => true, // 去除空格和换行
            'view_path'     => $this->addonPath . 'view' . DIRECTORY_SEPARATOR . 'plugin' . DIRECTORY_SEPARATOR,
            // 'view_path'     => $this->addonPath . 'view' . DIRECTORY_SEPARATOR,
            'view_dir_name' => 'view',
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

    // 获取插件下标签 addons/taglib文件
    protected function getTagLib() {
        return Cache::remember('addon_taglib', function(){
            $tagsArr = [];
            $addonsPath = $this->app->addons->getAddonsPath();
            
            foreach (scandir($addonsPath) as $name) {
                if (in_array($name, ['.', '..'])) continue;
                $taglibDir = $addonsPath . $name . DIRECTORY_SEPARATOR . 'taglib';
                if (!is_dir($taglibDir)) continue;
                
                foreach (glob($taglibDir . '/*.php') as $file) {
                    $className = pathinfo($file, PATHINFO_FILENAME);
                    $tagsArr[] = "\\addons\\{$name}\\taglib\\{$className}";
                }
            }
            return implode(',', $tagsArr);
        }, 3600); // 添加过期时间
    }

    /**
     * 插件基础信息
     * @param string|null $name 插件名
     * @return array
     */
    final public function getInfo(?string $name = '')
    {
        if(empty($name)) {
            $name = $this->getName();
        }

        $name = trim($name);
        if (empty($name)) {
            throw new \InvalidArgumentException('插件名称不能为空');
        }

        $configName = "addon_{$name}_info";
        //优先读取内存配置，没有就读ini文件
        $info = Config::get($configName, []);
        if (!empty($info)) {
            return $info;
        }

        // 文件配置
        $iniFile = addons_path() . $name . DIRECTORY_SEPARATOR . 'info.ini';
        if (!file_exists($iniFile)) {
            return [];
        }

        $_info = parse_ini_file($iniFile, true, INI_SCANNER_RAW) ?: [];
        $_info['url'] = addons_url();

        $info = array_merge($_info, $info);
        Config::set($info, $configName);

        return isset($info) ? $info : [];
    }

    /**
     * 设置插件信息数据
     * @param array $value 插件信息
     * @param string|null $name 插件名
     * @return array
     */
    final public function setInfo(array $value, ?string $name = ''): array
    {
        if(empty($name)) {
            $name = $this->getName();
        }

        $name = trim($name);
        if (empty($name)) {
            throw new \InvalidArgumentException('插件名称不能为空');
        }

        $info = $this->getInfo($name);
        $info = array_merge($info, $value);
        //校验必填字段
        if (!isset($info['name'], $info['title'], $info['version'])) {
            throw new \InvalidArgumentException("插件信息缺少必填字段[name,title,version]");
        }

        $configKey = "addon_{$name}_info";
        Config::set($info, $configKey);

        return $info;
    }

    /**
     * 获取配置信息
     * @param bool $type 是否获取完整配置
     * @return array
     */
    final public function getConfig(bool $type = false): array
    {
        $config = Config::get($this->addonConfig, []);
        if (!empty($config)) {
            return $config;
        }

        $configFile = $this->addonPath . 'config.php';

        if (!is_file($configFile)) {
            return [];
        }

        try {
            $tempArr = (array)(include $configFile);
        } catch (\Throwable $e) {
            // config.php语法错误直接返回空数组
            return [];
        }

        // 清除opcache，防止读取php缓存
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($configFile, true);
        }

        if ($type) {
            $result = $tempArr;
        } else {
            $result = [];
            foreach ($tempArr as $key => $item) {
                if (is_array($item) && isset($item['value'])) {
                    $result[$key] = $item['value'];
                } else {
                    $result[$key] = $item;
                }
            }
        }

        // 写入进程内存缓存
        Config::set($result, $this->addonConfig);

        return $result;
    }

    //必须实现安装
    abstract public function install();

    //必须卸载插件方法
    abstract public function uninstall();

    // 在 Addons.php 中补充
    abstract public function enabled();   // 启用插件

    abstract public function disabled();  // 禁用插件

    // 写入管理位
    protected function insert(array $hooks = []) {

        $methods = (array)get_class_methods("\\addons\\" . $this->name . "\\Plugin");
        if(!empty($hooks)) {
            foreach($hooks as $k => $v) {
                // 添加的方法不在类中跳过
                if(!in_array($k, $methods)) {
                    continue;
                }

                if(is_array($v)) {
                    foreach($v as $j) {
                        if(!is_int($j)) continue;
                        $result = Db::name('addon_hook')->where([
                            'hook_name' => $k,
                            'hook_type' => $j
                        ])->find();
                        if(is_null($result)) {
                            Db::name('addon_hook')->save([
                                'hook_name' => $k,
                                'hook_type' => $j
                            ]);
                        }
                    }
                } else {
                    if(!is_int($v)) continue;
                    $data = [
                        'hook_name' => $k,
                        'hook_type' => $v
                    ];
                    $res = Db::name('addon_hook')->where($data)->find();
    
                    if(is_null($res)) {
                        Db::name('addon_hook')->save($data);
                    }
                }
            }
        }
        return true;
    }

    // 移除管理位
    protected function remove(array $hooks = []) {

        if(!empty($hooks)) {
            foreach($hooks as $k => $v) {
                $res = Db::name('addon_hook')->where([
                    'hook_name' => $k
                ])->find();

                if(!is_null($res)) {
                    Db::name('addon_hook')->delete($res['id']);
                }
            }
        }
    }

}
