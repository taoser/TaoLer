<?php
declare(strict_types=1);

namespace taoser\addons;

use think\Route;
use think\helper\Str;
use think\facade\Config;
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

    // 注册系统服务，将服务绑定到容器中
    public function register()
    {
        $this->app->bind('addons', AddonsSystem::class);        

        // $this->initLazyLoader();
        
    }

    public function boot()
    {

        $addonsSystem = new AddonsSystem();

        $addonsSystem->autoload();
        // $addonsSystem->loadEvent();

        // 立即注册路由
        $route = $this->app->route;
        $execute = '\\taoser\\addons\\Route::execute';

        $middlewareArr = [Addons::class];
        // 注册插件公共中间件
        if (is_file($this->app->addons->getAddonsPath() . 'middleware.php')) {
            $middlewareArr = array_merge($middlewareArr, include $this->app->addons->getAddonsPath() . 'middleware.php');
        }

        // 注册插件控制器路由
        $route->rule("app/:addon/[:controller]/[:action]", $execute)->middleware($middlewareArr);
        
        /**** 监听注册路由
        // 监听注册路由
        $this->registerRoutes(function (Route $route) {
            
            // 路由脚本
            $execute = '\\taoser\\addons\\Route::execute';

            $middlewareArr = [Addons::class];
            // 注册插件公共中间件
            if (is_file($this->app->addons->getAddonsPath() . 'middleware.php')) {
                $middlewareArr = array_merge($middlewareArr, include $this->app->addons->getAddonsPath() . 'middleware.php');
            }

            // 注册插件控制器路由
            $route->rule("app/:addon/[:controller]/[:action]", $execute)->middleware($middlewareArr);

            // 自定义路由
            $routes = (array) Config::get('addons.route', []);

            if(!empty($routes)) {
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

        ****/

    }

    /**
     * 初始化延迟加载器
     */
    private function initLazyLoader(): void
    {
        $loader = HookLazyLoader::getInstance();
        
        $hooks = $this->app->isDebug() ? [] : Cache::get('hooks', []);
        
        if (empty($hooks)) {
            $hooks = (array) Config::get('addons.hooks', []);
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

        foreach ($hooks as $hookName => $listeners) {
            if (!empty($listeners)) {
                $loader->registerHook($hookName, $listeners);
            }
        }
    }


    /**
     * 注册延迟加载的钩子
     * 只在钩子首次被触发时才加载对应的监听器
     */
    private function registerLazyHooks(array $hooks): void
    {
        foreach ($hooks as $hookName => $listeners) {
            if (empty($listeners)) {
                continue;
            }

            Event::listen($hookName, function ($params) use ($hookName, $listeners) {
                return HookProxy::execute($hookName, $listeners, $params);
            });
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

}
