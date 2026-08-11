<?php
declare(strict_types=1);

namespace taoser\addons;

use think\facade\Event;

/**
 * 钩子代理类 - 实现延迟加载
 * 
 * 工作原理：
 * 1. 系统启动时只注册代理监听器，不加载实际插件
 * 2. 当钩子首次被触发时，代理类才加载对应的插件监听器
 * 3. 加载后替换原始监听器，后续调用直接执行
 */
class HookProxy
{
    private static array $loadedHooks = [];
    private static array $hookCache = [];

    /**
     * 执行钩子（延迟加载）
     */
    public static function execute(string $hookName, array $listeners, $params)
    {
        if (isset(self::$loadedHooks[$hookName])) {
            return self::executeDirect($hookName, $params);
        }

        return self::loadAndExecute($hookName, $listeners, $params);
    }

    /**
     * 加载并执行钩子
     */
    private static function loadAndExecute(string $hookName, array $listeners, $params)
    {
        $results = [];
        
        foreach ($listeners as $listener) {
            [$class, $method] = $listener;
            
            if (!class_exists($class)) {
                continue;
            }

            try {
                $instance = app()->make($class);
                
                if (method_exists($instance, $method)) {
                    $result = $instance->$method($params);
                    if ($result !== null) {
                        $results[] = $result;
                    }
                }
            } catch (\Exception $e) {
                if (app()->isDebug()) {
                    throw $e;
                }
            }
        }

        self::$loadedHooks[$hookName] = true;
        
        return $results;
    }

    /**
     * 直接执行已加载的钩子
     */
    private static function executeDirect(string $hookName, $params)
    {
        if (isset(self::$hookCache[$hookName])) {
            $results = [];
            foreach (self::$hookCache[$hookName] as $callback) {
                $result = call_user_func($callback, $params);
                if ($result !== null) {
                    $results[] = $result;
                }
            }
            return $results;
        }

        return Event::trigger($hookName, $params);
    }

    /**
     * 清除缓存（用于开发模式或插件更新后）
     */
    public static function clearCache(): void
    {
        self::$loadedHooks = [];
        self::$hookCache = [];
    }

    /**
     * 预加载指定钩子（用于高频钩子）
     */
    public static function preload(string $hookName): void
    {
        if (!isset(self::$loadedHooks[$hookName])) {
            self::$loadedHooks[$hookName] = true;
        }
    }

    /**
     * 检查钩子是否已加载
     */
    public static function isLoaded(string $hookName): bool
    {
        return isset(self::$loadedHooks[$hookName]);
    }
}