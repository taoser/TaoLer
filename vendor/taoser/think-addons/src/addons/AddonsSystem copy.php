<?php
declare(strict_types=1);

namespace taoser\addons;

use think\facade\Config;
use think\facade\Cache;
use think\facade\Lang;
use think\facade\Event;

class AddonsSystem
{
    protected $addonsPath;

    protected $app;

    public function __construct()
    {
        $this->app = app();

        // 初始化插件目录
        $this->addonsPath = $this->getAddonsPath();
         
    }


    /**
     * 自动载入插件
     * @return bool
     */
    public function autoload()
    {
        // 是否处理自动载入
        if (!Config::get('addons.autoload', true)) {
            return true;
        }

        $config = Config::get('addons');
        // 读取插件目录及钩子列表
        $base = get_class_methods("\\taoser\\Addons");
        $base = array_unique(array_merge($base, ['initialize', 'install', 'uninstall', 'enabled', 'disabled']));

        $addon_list = [];
        $bind = [];

        // 加载系统语言包
        Lang::load([
            __DIR__ . '/../lang/zh-cn.php'
        ]);

        // dump(glob($this->addonsPath . '*/*.php'));

        // 读取插件目录中的php文件
        foreach (glob($this->addonsPath . '*/*.php') as $addon_file) {
            // 格式化路径信息
            $info = pathinfo($addon_file);

            dump($info);
            // 获取插件目录名
            $name = pathinfo($info['dirname'], PATHINFO_FILENAME);

            dump($name);

            // 找到插件入口文件
            if (strtolower($info['filename']) === 'plugin') {
                // 读取出所有公共方法
                $methods = (array) get_class_methods("\\addons\\$name\\Plugin");

                $ini= $info['dirname'] . DIRECTORY_SEPARATOR . 'info.ini';
                if (!is_file($ini)) {
                    continue;
                }

                $addon_ini = parse_ini_file($ini, true, INI_SCANNER_TYPED) ?: [];
                
                // 排除未安装和未启用
                if(!$addon_ini['install']) continue;
                if(!$addon_ini['status']) continue;

                // 跟插件基类方法做比对，得到差异结果
                $hookArr = array_diff($methods, $base);
                // 循环将钩子方法写入配置中
                foreach ($hookArr as $hook) {
                    if (!isset($config['hooks'][$hook])) {
                        $config['hooks'][$hook] = [];
                    }
                    // 兼容手动配置项
                    if (is_string($config['hooks'][$hook])) {
                        $config['hooks'][$hook] = explode(',', $config['hooks'][$hook]);
                    }
                    if (!in_array($name, $config['hooks'][$hook])) {
                        $config['hooks'][$hook][] = $name;
                    }
                }

                if(!in_array($name, $addon_list)) {
                    $addon_list[] = $name;
                }

                // 插件目录
                $addonDir = $this->addonsPath . $name . DIRECTORY_SEPARATOR;

                    //路由配置文件
                $routeDir = $addonDir . 'route' .  DIRECTORY_SEPARATOR;
                if (file_exists($routeDir) && is_dir($routeDir)) {
                    $routeFiles = glob($routeDir . '*.php');
                    foreach ($routeFiles as $routeFile) {
                        if (file_exists($routeFile)) {
                            // $this->loadRoutesFrom($file);
                            include_once $routeFile;
                        }
                    }
                }

                // 语言配置文件目录
                $langDir = $addonDir . 'lang' . DIRECTORY_SEPARATOR;
                if (file_exists($langDir) && is_dir($langDir)) {
                    $langFiles = glob($langDir . '*.php');
                    $langFilesArray = [];
                    foreach ($langFiles as $lang_file) {
                        if (file_exists($lang_file)) {
                            $langFilesArray[] = $lang_file;
                        }
                    }
                    Lang::load($langFilesArray);
                }


                $hooks = $this->app->isDebug() ? [] : Cache::get('hooks', []);
    
                if (empty($hooks)) {
                    $hooks = (array) Config::get('addons.hooks', []);
                    // 初始化钩子
                    foreach ($hooks as $key => $values) {
                        if (is_string($values)) {
                            $values = explode(',', $values);
                        } else {
                            $values = (array) $values;
                        }
                        $hooks[$key] = array_filter(array_map(function ($v) use ($key) {
                            return [get_addons_class($v), $key];
                        }, $values));
                    }

                    Cache::set('hooks', $hooks);
                }
                //如果在插件中有定义 AddonsInit，则直接执行
                if (isset($hooks['AddonsInit'])) {
                    foreach ($hooks['AddonsInit'] as $k => $v) {
                        Event::trigger('AddonsInit', $v);
                    }
                }

                Event::listenEvents($hooks); // 111



                // 挂载插件服务
                $serviceFile = $addonDir . 'service.ini';
                if (is_file($serviceFile)) {
                    $info = parse_ini_file($serviceFile, true, INI_SCANNER_TYPED) ?: [];
                    $bind = array_merge($bind, $info);
                }
                
            }
        }

        $this->app->bind($bind);

        Cache::set('addons_config', $config);
        Cache::set('addons_list', $addon_list);

        Config::set($config, 'addons');
        
    }

    /**
     * 获取 addons 路径
     * @return string
     */
    public function getAddonsPath()
    {
        // 初始化插件目录
        $addonsPath = addons_path();

        // 如果插件目录不存在则创建
        if (!is_dir($addonsPath)) {
            @mkdir($addonsPath, 0755, true);
        }

        return $addonsPath;
    }
}