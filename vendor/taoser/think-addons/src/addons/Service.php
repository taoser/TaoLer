<?php
declare(strict_types=1);

namespace taoser\addons;

use think\Route;
use think\helper\Str;
use think\facade\Config;
use think\facade\Lang;
use think\facade\Cache;
use think\facade\Event;
use taoser\addons\middleware\Addons;

/**
 * 插件服务
 * Class Service
 * @package think\addons
 */
class Service extends \think\Service
{
    protected $addons_path;

    public function register()
    {
        $this->addons_path = $this->getAddonsPath();
        // 加载系统语言包
        $this->loadLang();
        // 自动载入插件
        $this->autoload();
        // 加载插件事件
        $this->loadEvent();
        // 加载自定义路由
        $this->loadRoutes();
        // 加载插件系统服务
        $this->loadService();
        // 加载插件命令
        $this->loadCommand();
        // 加载配置
        $this->loadConfig();
        // 绑定插件容器
        $this->app->bind('addons', Service::class);
<<<<<<< HEAD
        
=======

>>>>>>> 3.0
    }

    public function boot()
    {
        $this->registerRoutes(function (Route $route) {
            // 只有在addons下进行注册解析
            $path = $this->app->request->pathinfo();
            $pathArr = explode("/", str_replace('.html','', str_replace('\\', '/', $path)));
            if($pathArr[0] === 'addons') {
                // 路由脚本
                $execute = '\\taoser\\addons\\Route::execute';

                // 中间件数组
                $middlewaresArr = [];

                // 注册插件公共中间件
<<<<<<< HEAD
            if (is_file($this->app->addons->getAddonsPath() . 'middleware.php')) {
                $this->app->middleware->import(include $this->app->addons->getAddonsPath() . 'middleware.php', 'route');
=======
                if (is_file($this->app->addons->getAddonsPath() . 'middleware.php')) {
                    $this->app->middleware->import(include $this->app->addons->getAddonsPath() . 'middleware.php', 'route');
>>>>>>> 3.0
//
//                    // addons目录下全局中间件，对所有addons都生效
//                    //$middleware = (array) include $this->app->addons->getAddonsPath() . 'middleware.php';
//                    // 执行addons全局中间件
//                    //$route->rule("addons/:addon/[:controller]/[:action]", $execute)->middleware($middleware);
//                    //$middlewaresArr = array_merge($middlewaresArr, $middleware);
<<<<<<< HEAD
            }


//            $middlewareDir = $this->app->addons->getAddonsPath() . $addon. DIRECTORY_SEPARATOR . 'middleware' .  DIRECTORY_SEPARATOR;
                    // 如果插件下存在middleware文件夹
=======
                }


//            $middlewareDir = $this->app->addons->getAddonsPath() . $addon. DIRECTORY_SEPARATOR . 'middleware' .  DIRECTORY_SEPARATOR;
                // 如果插件下存在middleware文件夹
>>>>>>> 3.0
//            if(is_dir($middlewareDir)) {
//                //配置
//                $middleware_dir = scandir($middlewareDir);
//                foreach ($middleware_dir as $name) {
//                    if (in_array($name, ['.', '..'])) {
//                        continue;
//                    }
//                    if(is_dir($middlewareDir . $name)) continue;
//                    $middlewareClassName = str_replace('.php','',$name);
//                    $middlewareClass = "\\addons\\{$addon}\\middleware\\{$middlewareClassName}";
//
//                    array_push($middlewaresArr, $middlewareClass);
//                }
//            }

                // 注册控制器路由
                $route->rule("addons/:addon/[:controller]/[:action]", $execute)->middleware(Addons::class);

                // 自定义路由
                $routes = (array) Config::get('addons.route', []);
                foreach ($routes as $key => $val) {
                    if (!$val) {
                        continue;
                    }
                    if (is_array($val)) {
                        $domain = $val['domain'];
                        $rules = [];
                        foreach ($val['rule'] as $k => $rule) {
                            [$addon, $controller, $action] = explode('/', $rule);
                            $rules[$k] = [
                                'addon'        => $addon,
                                'controller'    => $controller,
                                'action'        => $action,
                                'indomain'      => 1,
                            ];
                        }
                        $route->domain($domain, function () use ($rules, $route, $execute) {
                            // 动态注册域名的路由规则
                            foreach ($rules as $k => $rule) {
                                $route->rule($k, $execute)
                                    ->name($k)
                                    ->completeMatch(true)
                                    ->append($rule);
                            }
                        });
                    } else {
                        list($addon, $controller, $action) = explode('/', $val);
                        $route->rule($key, $execute)
                            ->name($key)
                            ->completeMatch(true)
                            ->append([
                                'addon' => $addon,
                                'controller' => $controller,
                                'action' => $action
                            ]);
                    }
                }
            }
        });
    }

    private function loadLang()
    {
        Lang::load([
            $this->app->getRootPath() . '/vendor/taoser/think-addons/src/lang/zh-cn.php'
        ]);
    }

    /**
     *  自定义路由文件
     */
    private function loadRoutes()
    {
        //配置
        $addons_dir = scandir($this->addons_path);
        foreach ($addons_dir as $name) {
            if (in_array($name, ['.', '..'])) {
                continue;
            }
            if(!is_dir($this->addons_path . $name)) continue;
            $module_dir = $this->addons_path . $name .  DIRECTORY_SEPARATOR;
            //路由配置文件
            $addons_route_dir = $module_dir . 'route' .  DIRECTORY_SEPARATOR;

            if (file_exists($addons_route_dir) && is_dir($addons_route_dir)) {
                $files = glob($addons_route_dir . '*.php');
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        $this->loadRoutesFrom($file);;
                    }
                }
            }
        }
    }

    /**
     * 加载插件配置文件
     */
    private function loadConfig()
    {
        $results = scandir($this->addons_path);
        foreach ($results as $name) {
            if (in_array($name, ['.', '..'])) continue;
            if (!is_dir($this->addons_path . $name)) continue;
            foreach (scandir($this->addons_path . $name) as $childname) {
                if (in_array($childname, ['.', '..', 'public', 'view'])) {
                    continue;
                }

                $commands = [];
                //配置文件
                $addons_config_dir = $this->addons_path . $name . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;

                if (is_dir($addons_config_dir)) {
                    $files = glob($addons_config_dir . '*.php');
                    foreach ($files as $file) {
                        if (file_exists($file)) {
                            if (substr($file, -11) == 'console.php') {
                                $commands_config = include_once $file;
                                isset($commands_config['commands']) && $commands = array_merge($commands, $commands_config['commands']);
                                !empty($commands) && $this->commands($commands);
                            }
                        }
                    }
                }

            }
        }
    }

    /**
     * 插件事件
     */
    private function loadEvent()
    {
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
        Event::listenEvents($hooks);
    }

    /**
     * 挂载插件服务
     */
    private function loadService()
    {
        $results = scandir($this->addons_path);
        $bind = [];
        foreach ($results as $name) {
            if ($name === '.' or $name === '..') {
                continue;
            }
            if (is_file($this->addons_path . $name)) {
                continue;
            }
            $addonDir = $this->addons_path . $name . DIRECTORY_SEPARATOR;
            if (!is_dir($addonDir)) {
                continue;
            }

            if (!is_file($addonDir . ucfirst($name) . '.php')) {
                continue;
            }

            $info_file = $addonDir . 'info.ini';
            if (!is_file($info_file)) {
                continue;
            }
            $info = parse_ini_file($info_file, true, INI_SCANNER_TYPED) ?: [];
            $bind = array_merge($bind, $info);
        }
        $this->app->bind($bind);
    }

    /**
     * 自动载入插件
     * @return bool
     */
    private function autoload()
    {
        // 是否处理自动载入
        if (!Config::get('addons.autoload', true)) {
            return true;
        }
        $config = Config::get('addons');
        // 读取插件目录及钩子列表
        $base = get_class_methods("\\taoser\\Addons");
        // 读取插件目录中的php文件
        foreach (glob($this->getAddonsPath() . '*/*.php') as $addons_file) {
            // 格式化路径信息
            $info = pathinfo($addons_file);
            // 获取插件目录名
            $name = pathinfo($info['dirname'], PATHINFO_FILENAME);
            // 找到插件入口文件
            if (strtolower($info['filename']) === 'plugin') {
                // 读取出所有公共方法
                $methods = (array)get_class_methods("\\addons\\" . $name . "\\" . $info['filename']);
                // 跟插件基类方法做比对，得到差异结果
                $hooks = array_diff($methods, $base);
                // 循环将钩子方法写入配置中
                foreach ($hooks as $hook) {
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
            }
        }
        Config::set($config, 'addons');
    }

    /**
     * 加载插件命令
     */
    private function loadCommand()
    {
        $results = scandir($this->addons_path);
        foreach ($results as $name) {
            if ($name === '.' or $name === '..') {
                continue;
            }
            if (is_file($this->addons_path . $name)) {
                continue;
            }
            $addonDir = $this->addons_path . $name . DIRECTORY_SEPARATOR;
            if (!is_dir($addonDir)) {
                continue;
            }
            $command_file = $addonDir . 'command.php';
            if (is_file($command_file)) {
                $commands = include_once $command_file;
                if (is_array($commands))
                    $this->commands($commands);
            }
        }
    }

    /**
     * 获取 addons 路径
     * @return string
     */
    public function getAddonsPath()
    {
        // 初始化插件目录
        $addons_path = $this->app->getRootPath() . 'addons' . DIRECTORY_SEPARATOR;
        // 如果插件目录不存在则创建
        if (!is_dir($addons_path)) {
            @mkdir($addons_path, 0755, true);
        }

        return $addons_path;
    }

    /**
     * 获取插件的配置信息
     * @param string $name
     * @return array
     */
    public function getAddonsConfig()
    {
        $name = $this->app->request->addon;
        $addon = get_addons_instance($name);
        if (!$addon) {
            return [];
        }

        return $addon->getConfig();
    }
}
