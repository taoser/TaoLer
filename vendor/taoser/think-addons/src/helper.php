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
use think\exception\InvalidArgumentException;

define('DS', DIRECTORY_SEPARATOR);

\think\Console::starting(function (\think\Console $console) {
    $console->addCommands([
        'addons:config' => '\\taoser\\addons\\command\\SendConfig',
        'addons:clear'  => '\\taoser\\addons\\command\\ClearAddons',
    ]);
});

spl_autoload_register(function ($class) {
    static $baseDir = null;
    if ($baseDir === null) {
        $root_path = str_replace('\\', '/', dirname(__DIR__));
        $baseDir = strstr($root_path, 'vendor', true) . 'addons/';
    }

    if (strncmp($class, 'addons\\', 7) !== 0) {
        return false;
    }

    $class = substr($class, 7);
    $path = '';
    if (($pos = strripos($class, '\\')) !== false) {
        $path = str_replace('\\', '/', substr($class, 0, $pos)) . '/';
        $class = substr($class, $pos + 1);
    }
    $path .= str_replace('_', '/', $class) . '.php';
    $file = $baseDir . $path;

    if (is_file($file)) {
        include $file;
        return true;
    }
    return false;
});

// if (!function_exists('hook')) {
//     /**
//      * 处理插件钩子
//      * @param string $event 钩子名称
//      * @param array|null $params 传入参数
//      * @param bool $once 是否只返回一个结果
//      * @return mixed
//      */
//     function hook($event, &$params = null, bool $once = false)
//     {
//         $result = Event::trigger($event, $params, $once);

//         return join('', $result);
//     }
// }

if (!function_exists('hook')) {
    /**
     * 处理插件钩子（支持延迟加载）
     * @param string $event 钩子名称
     * @param array|null $params 传入参数
     * @param bool $once 是否只返回一个结果
     * @return mixed
     */
    function hook($event, &$params = null, bool $once = false)
    {
        app()->addons->ensureHooksLoaded();

        $loader = \taoser\addons\HookLazyLoader::getInstance();

        if (PHP_SAPI === 'cli' && $loader->isInitialized()) {
            static $requestId = '';
            $currentId = spl_object_id(app()->request);
            if ($requestId !== $currentId) {
                $requestId = $currentId;
                $loader->resetForNewRequest();
            }
        }

        $result = $loader->executeHook($event, $params);

        if ($once && is_array($result)) {
            return $result[0] ?? null;
        }

        return is_array($result) ? join('', $result) : $result;
    }
}

if (!function_exists('get_addons_class')) {
    /**
     * 获取插件类的类名
     * @param string $name 插件名
     * @param string $type 'controller' | 'hook' 返回命名空间类型
     * @param string|null $class 当前类名
     * @return string 返回空字符串代表类不存在
     */
    function get_addons_class(string $name, string $type = 'hook', ?string $class = null): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        // 处理多级控制器情况
        if (!is_null($class) && str_contains($class, '.')) {
            $parts = explode('.', $class);
            $last = array_pop($parts);
            $parts[] = Str::studly($last);
            $class = implode('\\', $parts);
        } else {
            $class = Str::studly(is_null($class) ? $name : $class);
        }

        switch ($type) {
            case 'controller':
                $namespace = '\\addons\\' . $name . '\\controller\\' . $class;
                // 匹配空控制器
                if (!class_exists($namespace)) {
                    $emptyController = config('route.empty_controller', '');
                    if ($emptyController !== '') {
                        $namespace = '\\addons\\' . $name . '\\controller\\' . $emptyController;
                    }
                }
                break;
            default:
                $namespace = '\\addons\\' . $name . '\\Plugin';
                break;
        }

        return class_exists($namespace) ? $namespace : '';
    }
}

if (!function_exists('get_addons_instance')) {
    $GLOBALS['_addons_instances'] = [];

    /**
     * 获取插件的单例
     * @param string $name 插件名
     * @return object|null
     */
    function get_addons_instance(string $name): ?object
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        if (array_key_exists($name, $GLOBALS['_addons_instances'])) {
            $instance = $GLOBALS['_addons_instances'][$name];
            if (method_exists($instance, 'setRequest')) {
                $instance->setRequest(app()->request);
            }
            return $instance;
        }

        $class = get_addons_class($name);
        if (!$class || !class_exists($class)) {
            return null;
        }

        try {
            $instance = new $class(app());
            $GLOBALS['_addons_instances'][$name] = $instance;
            return $instance;
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('clear_addons_instance_cache')) {
    /**
     * 清除插件单例缓存，workerman环境插件更新调用
     * @param string|null $name 指定插件名，null清空全部
     * @return void
     */
    function clear_addons_instance_cache(?string $name = null): void
    {
        if ($name) {
            unset($GLOBALS['_addons_instances'][$name]);
        } else {
            $GLOBALS['_addons_instances'] = [];
        }

        \taoser\addons\HookLazyLoader::getInstance()->reset();
    }
}

if (!function_exists('get_addons_taglib')) {
    /**
     * 获取所有插件的标签库类名（逗号分隔），用于 View::config taglib_pre_load
     * @return string
     */
    function get_addons_taglib(): string
    {
        return \think\facade\Cache::remember('addon_taglib', function () {
            $tagsArr = [];
            $addonsPath = app()->addons->getAddonsPath();

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
        }, 3600);
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
    function addons_url($url = '', $param = [], $suffix = false, $domain = false)
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

        return Route::buildUrl("/app/{$addons}/{$controller}/{$action}", $param)->suffix($suffix)->domain($domain);
    }
}

if (!function_exists('get_addons_info')) {
    /**
     * 读取插件的基础信息
     * @param string $name 插件名
     * @return array
     */
    function get_addons_info($name): array
    {
        // 读取缓存，避免每次请求 parse_ini_file
        $name = trim($name);
        if ($name === '') {
            return [];
        }

        $cacheKey = 'addon_info_' . $name;
        $info = Cache::get($cacheKey);
        if (is_array($info)) {
            return $info;
        }

        $file = addons_path() . $name . DIRECTORY_SEPARATOR . 'info.ini';
        if (!file_exists($file)) {
            return [];
        }

        $info = parse_ini_file($file, true, INI_SCANNER_RAW) ?: [];
        Cache::set($cacheKey, $info, 3600);

        return $info;
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
        $name = trim($name);
        if (empty($name)) {
            throw new \Exception("插件名称不能为空");
        }

        $addonDir = addons_path() . $name . DIRECTORY_SEPARATOR;
        $file = $addonDir . 'info.ini';

        // 检测插件目录，不存在尝试创建
        if (!is_dir($addonDir)) {
            if (!mkdir($addonDir, 0755, true)) {
                throw new \Exception("插件目录创建失败，无权限：{$addonDir}");
            }
        }

        // 获取插件实例，允许实例不存在（部分场景仅写ini不需要插件实例）
        $addon = get_addons_instance($name);
        if ($addon !== null && method_exists($addon, 'setInfo')) {
            $array = $addon->setInfo($array, $name);
        }

        // 检查必须字段是否存在
        if (!isset($array['name'], $array['title'], $array['version'])) {
            throw new Exception("Failed to write plugin config");
        }

        // 状态变更钩子，仅实例存在时调用
        if ($addon !== null && method_exists($addon, 'enabled') && method_exists($addon, 'disabled')) {
            !empty($array['status']) ? $addon->enabled() : $addon->disabled();
        }

        $res = [];
        foreach ($array as $key => $val) {
            // 过滤非法key，ini key不能包含 = # \n
            $key = trim((string)$key);
            if ($key === '' || strpbrk($key, "=#\n\r")) {
                continue;
            }

            if (is_array($val)) {
                $res[] = "[$key]";
                foreach ($val as $k => $v) {
                    $k = trim((string)$k);
                    if ($k === '' || strpbrk($k, "=#\n\r")) {
                        continue;
                    }
                    $res[] = sprintf("%s = %s", $k, _ini_value_escape($v));
                }
            } else {
                $res[] = sprintf("%s = %s", $key, _ini_value_escape($val));
            }
        }

        // 写入ini文件
        $content = implode("\n", $res) . "\n";
        $handle = fopen($file, 'w');
        if (!$handle) {
            throw new \Exception("File does not have write permission：{$file}");
        }
        fwrite($handle, $content);
        fclose($handle);

        // 更新内存配置缓存
        Config::set($array, "addon_{$name}_info");

        // 清理插件单例缓存，Workerman常驻环境更新后生效
        if (function_exists('clear_addons_instance_cache')) {
            clear_addons_instance_cache($name);
        }

        // 清理插件信息/列表/钩子/清单缓存（统一键名收口）
        Cache::delete('addon_info_' . $name);
        Cache::delete('addonslist');
        Cache::delete('addons_list');
        Cache::delete('hooks');
        Cache::delete('addons_manifest');

        return true;
    }
}

/**
 * 内部辅助：ini值转义处理，兼容bool/float/int/string，处理特殊字符
 * @param mixed $value
 * @return string
 */
if (!function_exists('_ini_value_escape')) {
    function _ini_value_escape($value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value)) {
            return (string)$value;
        }
        if (is_float($value)) {
            return sprintf("%.1f", $value);
        }
        // null转为空字符串
        if ($value === null) {
            return '';
        }
        $str = (string)$value;
        // 删除换行，ini不允许值内换行
        $str = str_replace(["\r", "\n"], ' ', $str);
        // 包含 = # 空格，使用双引号包裹
        if (strpbrk($str, '=#" ') !== false) {
            // 转义双引号
            $str = str_replace('"', '\\"', $str);
            return "\"{$str}\"";
        }
        return $str;
    }
}


if (!function_exists('get_addons_config')) {
    /**
     * 获取插件配置，优先插件实例getConfig，实例不存在直接读取config.php磁盘文件降级
     * @param string $name 插件名
     * @param bool $type 是否返回原始数组（包含value字段）
     * @return array
     */
    function get_addons_config(string $name, bool $type = false): array
    {
        $name = trim($name);
        if ($name === '') {
            return [];
        }

        $addon = get_addons_instance($name);
        if ($addon && method_exists($addon, 'getConfig')) {
            return $addon->getConfig($type);
        }

       // 插件实例获取失败，降级直接读取磁盘 config.php
        $addonsPath = app()->getAddonsPath();
        $configFile = $addonsPath . $name . DIRECTORY_SEPARATOR . 'config.php';
        if (!is_file($configFile)) {
            return [];
        }
        // 仅常驻进程（Workerman/Swoole，PHP_SAPI=cli）需要主动失效 opcache；
        // PHP-FPM 下文件变更由 opcache.validate_timestamps 自动重编译，主动失效反而降低命中
        if (PHP_SAPI === 'cli' && function_exists('opcache_invalidate')) {
            opcache_invalidate($configFile, true);
        }
        
        $config = include $configFile;
        return is_array($config) ? $config : [];
    }
}

if (!function_exists('set_addons_config')) {
    /**
     * 设置插件config.php配置文件
     * @param string $name 插件名
     * @param array $array 配置数组
     * @return bool
     * @throws \Exception
     */
    function set_addons_config(string $name, array $array): bool
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('插件名称不能为空');
        }
        
        // 简单防护，防止路径穿越
        if (strpbrk($name, DIRECTORY_SEPARATOR . '/\\')) {
            throw new \Exception('插件名称非法');
        }

        // $addonsPath = app()->getAddonsPath();
        $addonsPath = addons_path();
        $pluginDir  = $addonsPath . $name . DIRECTORY_SEPARATOR;
        $file       = $pluginDir . 'config.php';

        // 插件目录不存在则创建
        if (!is_dir($pluginDir)) {
            if (!mkdir($pluginDir, 0755, true)) {
                throw new \Exception("插件目录创建失败，无权限：{$pluginDir}");
            }
        }

        // 检查目录可写，文件不存在则判断目录权限
        if (is_file($file)) {
            if (!is_writable($file)) {
                throw new \Exception('config.php 文件无写入权限');
            }
        } else {
            if (!is_writable($pluginDir)) {
                throw new \Exception('config.php File does not have write permission');
            }
        }

        if (!class_exists(\Symfony\Component\VarExporter\VarExporter::class)) {
            throw new \Exception('VarExporter 类不存在，请安装 symfony/var-exporter');
        }
        
        $handle = fopen($file, 'w');
        if (!$handle) {
            throw new \Exception('File does not have write permission：' . $file);
        }

        // $exportContent = "<?php\n\nreturn " . \Symfony\Component\VarExporter\VarExporter::export($array) . ";\n";
        fwrite($handle, "<?php\n\n" . "return " . VarExporter::export($array) . ";\n");
        fclose($handle);

        // 清除当前进程opcache，立刻刷新本进程的php配置缓存
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }

        // 清理插件单例缓存，下次获取实例重新加载配置
        if (function_exists('clear_addons_instance_cache')) {
            clear_addons_instance_cache($name);
        }
        
        return true;
    }
}

if (!function_exists('get_addons_menu')) {
    /**
     * 获取插件菜单
     * @param string $name 插件名
     * @return array
     */
    function get_addons_menu(string $name): array
    {
        $menu = addons_path() . $name . DIRECTORY_SEPARATOR . 'menu.php';
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
        return Cache::remember('addonslist', function () {
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
            return $list;
        }, 3600);
    }

}

if (!function_exists('addons_path')) {
    /**
     * 获取插件目录路径
     *
     * @param string $path 插件路径
     * @return string
     */
    function addons_path(string $path = '') {
        return app()->getRootPath() .  'addons' . DIRECTORY_SEPARATOR . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
}