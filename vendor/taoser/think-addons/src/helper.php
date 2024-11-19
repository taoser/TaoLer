<?php
declare(strict_types=1);

use think\facade\Event;
use think\facade\Route;
use taoser\addons\Service;
use think\facade\App;
use think\facade\Config;
use think\facade\Cache;
use think\helper\{
    Str, Arr
};
use Symfony\Component\VarExporter\VarExporter;

define('DS', DIRECTORY_SEPARATOR);

\think\Console::starting(function (\think\Console $console) {
    $console->addCommands([
        'addons:config' => '\\taoser\\addons\\command\\SendConfig'
    ]);
});

// 插件类库自动载入
spl_autoload_register(function ($class) {

    $class = ltrim($class, '\\');

    //$dir = App::getRootPath();
    $root_path = str_replace('\\','/', dirname(__DIR__));
    $dir = strstr($root_path, 'vendor', true);
    $namespace = 'addons';

    if (strpos($class, $namespace) === 0) {
        $class = substr($class, strlen($namespace));
        $path = '';
        if (($pos = strripos($class, '\\')) !== false) {
            $path = str_replace('\\', '/', substr($class, 0, $pos)) . '/';
            $class = substr($class, $pos + 1);
        }
        $path .= str_replace('_', '/', $class) . '.php';
        $dir .= $namespace . $path;

        if (file_exists($dir)) {
            include $dir;
            return true;
        }
        return false;
    }
    return false;
});

if (!function_exists('hook')) {
    /**
     * 处理插件钩子
     * @param string $event 钩子名称
     * @param array|null $params 传入参数
     * @param bool $once 是否只返回一个结果
     * @return mixed
     */
    function hook($event, $params = null, bool $once = false)
    {
        $result = Event::trigger($event, $params, $once);

        return join('', $result);
    }
}

if (!function_exists('get_addons_info')) {
    /**
     * 读取插件的基础信息
     * @param string $name 插件名
     * @return array
     */
    function get_addons_info($name)
    {
        $addon = get_addons_instance($name);
        if (!$addon) {
            return [];
        }

        return $addon->getInfo();
    }
}


if (!function_exists('get_addons_instance')) {
    /**
     * 获取插件的单例
     * @param string $name 插件名
     * @return mixed|null
     */
    function get_addons_instance($name)
    {
        static $_addons = [];
        if (isset($_addons[$name])) {
            return $_addons[$name];
        }
        $class = get_addons_class($name);
        if (class_exists($class)) {
            $_addons[$name] = new $class(app());

            return $_addons[$name];
        } else {
            return null;
        }
    }
}

if (!function_exists('get_addons_class')) {
    /**
     * 获取插件类的类名
     * @param string $name 插件名
     * @param string $type 返回命名空间类型
     * @param string $class 当前类名
     * @return string
     */
    function get_addons_class($name, $type = 'hook', $class = null)
    {
        $name = trim($name);
        // 处理多级控制器情况
        if (!is_null($class) && strpos($class, '.')) {
            $class = explode('.', $class);

            $class[count($class) - 1] = Str::studly(end($class));
            $class = implode('\\', $class);
        } else {
            $class = Str::studly(is_null($class) ? $name : $class);
        }
        switch ($type) {
            case 'controller':
                $namespace = '\\addons\\' . $name . '\\controller\\' . $class;
                // 匹配空控制器
                if (!class_exists($namespace)) {
                    $namespace = '\\addons\\' . $name . '\\Controller\\' . config('route.empty_controller');
                }
                break;
            default:
                $namespace = '\\addons\\' . $name . '\\Plugin';
        }

        return class_exists($namespace) ? $namespace : '';
    }
}

if (!function_exists('addons_url')) {
    /**
     * 插件显示内容里生成访问插件的url
     * @param $url
     * @param array $param
     * @param bool|string $suffix 生成的URL后缀
     * @param bool|string $domain 域名
     * @return bool|string
     */
    function addons_url($url = '', $param = [], $suffix = true, $domain = false)
    {
        $request = app('request');
        if (empty($url)) {
            // 生成 url 模板变量
            $addons = $request->addon;
            $controller = $request->controller();
            $controller = str_replace('/', '.', $controller);
            $action = $request->action();
        } else {
            $url = Str::studly($url);
            $url = parse_url($url);
            if (isset($url['scheme'])) {
                $addons = strtolower($url['scheme']);
                $controller = $url['host'];
                $action = trim($url['path'], '/');
            } else {
                $route = explode('/', $url['path']);
                $addons = $request->addon;
                $action = array_pop($route);
                $controller = array_pop($route) ?: $request->controller();
            }
            $controller = Str::snake((string)$controller);

            /* 解析URL带的参数 */
            if (isset($url['query'])) {
                parse_str($url['query'], $query);
                $param = array_merge($query, $param);
            }
        }

        return Route::buildUrl("@addons/{$addons}/{$controller}/{$action}", $param)->suffix($suffix)->domain($domain);
    }
}

if (!function_exists('set_addons_info')) {
    /**
     * 设置基础配置信息
     * @param string $name 插件名
     * @param array $array 配置数据
     * @return boolean
     * @throws Exception
     */
    function set_addons_info($name, $array)
    {
        $service = new Service(App::instance()); // 获取service 服务
        $addons_path = $service->getAddonsPath();
        // 插件列表
        $file = $addons_path . $name . DIRECTORY_SEPARATOR . 'info.ini';
        $addon = get_addons_instance($name);
        $array = $addon->setInfo($name, $array);
        $array['status'] ? $addon->enabled() : $addon->disabled();
        if (!isset($array['name']) || !isset($array['title']) || !isset($array['version'])) {
            throw new Exception("Failed to write plugin config");
        }
        $res = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $res[] = "[$key]";
                foreach ($val as $k => $v)
                    $res[] = "$k = " . (is_numeric($v) ? $v : $v);
            } else
                $res[] = "$key = " . (is_numeric($val) ? $val : $val);
        }

        if ($handle = fopen($file, 'w')) {
            fwrite($handle, implode("\n", $res) . "\n");
            fclose($handle);
            //清空当前配置缓存
            Config::set($array, "addon_{$name}_info");
            Cache::delete('addonslist');
        } else {
            throw new Exception("File does not have write permission");
        }
        return true;
    }
}


if (!function_exists('get_addons_config')) {
    /**
     * 获取插件的配置
     * @param string $name 插件名
     * @return mixed|null
     */
    function get_addons_config($name)
    {
        $addon = get_addons_instance($name);
        if (!$addon) {
            return [];
        }

        return $addon->getConfig($name);
    }
}

if (!function_exists('set_addons_config')) {
    /**
     * 设置插件配置文件
     * @param string $name
     * @param array $array
     * @return bool
     * @throws Exception
     */
    function set_addons_config(string $name, array $array)
    {
        $service = new Service(App::instance()); // 获取service 服务
        $addons_path = $service->getAddonsPath();
        // 插件列表
        $file = $addons_path . $name . DIRECTORY_SEPARATOR . 'config.php';
        if (!is_writable($file)) {
            throw new \Exception(lang("addons.php File does not have write permission"));
        }
        if ($handle = fopen($file, 'w')) {
            fwrite($handle, "<?php\n\n" . "return " . VarExporter::export($array) . ";\n");
            fclose($handle);
        } else {
            throw new Exception(lang("File does not have write permission"));
        }
        return true;
    }
}

if (!function_exists('get_addons_menu')) {
    /**
     * 获取插件菜单
     * @param $name
     * @return array|mixed
     */
    function get_addons_menu($name)
    {
        $menu = app()->getRootPath() . 'addons' . DS . $name . DS . 'menu.php';
        if(file_exists($menu)){
            return include_once $menu;
        }
        return [];
    }
}

if (!function_exists('get_addons_list')) {
    /**
     * 获得插件列表
     * @return array
     */
    function get_addons_list()
    {
        $list = Cache::get('addonslist');
        if (empty($list)) {
            $addonsPath = app()->getRootPath().'addons'.DS; // 插件列表
            $results = scandir($addonsPath);
            $list = [];
            foreach ($results as $name) {
                if ($name === '.' or $name === '..')
                    continue;
                if (is_file($addonsPath . $name))
                    continue;
                $addonDir = $addonsPath . $name . DS;
                if (!is_dir($addonDir))
                    continue;
                if (!is_file($addonDir . 'Plugin' . '.php'))
                    continue;
                $info = get_addons_info($name);
                if (!isset($info['name']))
                    continue;
                //$info['url'] =isset($info['url']) && $info['url'] ?(string)addons_url($info['url']):'';
                $list[$name] = $info;
            }
            Cache::set('addonslist', $list);
        }
        return $list;
    }

}