<?php
declare(strict_types=1);

namespace taoser\addons;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Lang;
use think\facade\Event;
use think\App;

class AddonsSystem extends \think\Service
{
    protected $addonsPath;

    /**
     * 构造函数：初始化插件系统服务
     *
     * @param App|null $app 应用实例，null 时自动获取单例（兼容常驻内存）
     */
    public function __construct(?App $app = null)
    {
        parent::__construct($app ?? App::getInstance());

        $this->addonsPath = $this->getAddonsPath();
    }

    /** @var bool 钩子系统是否已加载（确保 ensureHooksLoaded 仅执行一次） */
    private bool $hooksLoaded = false;

    /**
     * 自动载入插件（轻量级，仅注册路由和服务绑定）
     *
     * 在 Service::boot() 中调用，每个请求执行一次。
     * 仅做路由注册 + 服务绑定 + 命令注册，不初始化钩子系统。
     * 钩子初始化延迟到首次 hook() 调用时（ensureHooksLoaded），
     * 使纯 API 请求零插件开销。
     */
    public function autoload()
    {
        if (!Config::get('addons.autoload', true)) {
            return;
        }

        $manifest = $this->getManifest();

        if (!empty($manifest['bind'])) {
            $this->app->bind($manifest['bind']);
        }

        foreach ($manifest['routeFiles'] as $routeFile) {
            if (is_file($routeFile)) {
                include $routeFile;
            }
        }

        if (PHP_SAPI === 'cli' && !empty($manifest['commands'])) {
            $this->commands($manifest['commands']);
        }
    }

    public function ensureHooksLoaded(): void
    {
        if ($this->hooksLoaded) {
            return;
        }
        $this->hooksLoaded = true;

        Lang::load([__DIR__ . '/../lang/zh-cn.php']);

        $manifest = $this->getManifest();

        $config = (array) Config::get('addons', []);
        $config['hooks'] = array_merge((array) ($config['hooks'] ?? []), $manifest['hooks']);
        Config::set($config, 'addons');

        if (!empty($manifest['langFiles'])) {
            Lang::load($manifest['langFiles']);
        }

        $this->initHookLazyLoader($manifest['hooks'] ?? []);

        if (!empty($manifest['hooks']['AddonsInit'])) {
            \hook('AddonsInit', $manifest['hooks']['AddonsInit']);
        }
    }

    /**
     * 获取插件 manifest（带缓存）
     *
     * 生产环境：86400s 长缓存，指纹变化自动重建。
     * 调试模式：5s 短缓存，开发即时生效的同时避免每请求全量扫描。
     *
     * @return array manifest 数据（hooks/addon_list/bind/routeFiles/langFiles/commands）
     */
    private function getManifest(): array
    {
        $cacheKey = 'addons_manifest';

        if (!$this->app->isDebug()) {
            $manifest = Cache::get($cacheKey);
            if (is_array($manifest) && isset($manifest['fp'])) {
                return $manifest;
            }
        } else {
            $manifest = Cache::get($cacheKey);
            if (is_array($manifest) && ($manifest['fp'] ?? '') === $this->fingerprint()) {
                return $manifest;
            }
        }

        $manifest = $this->scanAddons();
        $manifest['fp'] = $this->fingerprint();
        Cache::set($cacheKey, $manifest, 86400);

        return $manifest;
    }

    /**
     * 初始化钩子懒加载器
     *
     * 将原始钩子映射（hookName → addonNames）转换为类级映射（hookName → [class, method]），
     * 注册到 Event 系统和 HookLazyLoader。非调试模式下缓存到 Cache。
     *
     * @param array $rawHooks 原始钩子映射 ['hookName' => ['addon1', 'addon2', ...]]
     */
    private function initHookLazyLoader(array $rawHooks): void
    {
        $loader = HookLazyLoader::getInstance();
        if ($loader->isInitialized()) {
            return;
        }

        $hooks = [];
        foreach ($rawHooks as $hookName => $addonNames) {
            foreach ((array) $addonNames as $addonName) {
                $hooks[$hookName][] = [$addonName, $hookName];
            }
        }

        $loader->initialize($hooks);
    }

    /**
     * 插件目录指纹：目录名 + info.ini 修改时间
     * 插件安装/卸载/启用/禁用（都会重写 info.ini）时自动变化，无需手动清缓存
     * @return string
     */
    private function fingerprint(): string
    {
        static $cached = null;
        static $cachedAt = 0;

        $now = time();
        if ($cached !== null && ($now - $cachedAt) < 60) {
            return $cached;
        }

        $parts = [];
        foreach (scandir($this->addonsPath) as $name) {
            if ($name === '.' || $name === '..' || !is_dir($this->addonsPath . $name)) {
                continue;
            }
            $ini = $this->addonsPath . $name . DIRECTORY_SEPARATOR . 'info.ini';
            if (is_file($ini)) {
                $parts[] = $name . ':' . filemtime($ini);
            }
        }

        $cached = md5(implode('|', $parts));
        $cachedAt = $now;
        return $cached;
    }

    /**
     * 全量扫描插件，收集钩子/服务绑定/路由/语言/命令清单（原 autoload 的重活）
     * @return array
     */
    private function scanAddons(): array
    {
        $manifest = [
            'hooks'      => [],
            'addon_list' => [],
            'bind'       => [],
            'routeFiles' => [],
            'langFiles'  => [],
            'commands'   => [],
        ];

        foreach (scandir($this->addonsPath) as $name) {
            if ($name === '.' || $name === '..' || !is_dir($this->addonsPath . $name)) {
                continue;
            }

            $addonDir = $this->addonsPath . $name . DIRECTORY_SEPARATOR;

            if (!is_file($addonDir . 'Plugin.php') || !is_file($addonDir . 'info.ini')) {
                continue;
            }

            $addonInfo = parse_ini_file($addonDir . 'info.ini', true, INI_SCANNER_TYPED) ?: [];
            if (empty($addonInfo['install']) || empty($addonInfo['status'])) {
                continue;
            }

            if (!empty($addonInfo['hooks']) && is_array($addonInfo['hooks'])) {
                foreach ($addonInfo['hooks'] as $hook => $addonName) {
                    $manifest['hooks'][$hook][] = $name;
                }
            } else {
                static $baseMethods = null;
                if ($baseMethods === null) {
                    $baseMethods = array_unique(array_merge(
                        get_class_methods(\taoser\Addons::class),
                        ['initialize', 'install', 'uninstall', 'enabled', 'disabled']
                    ));
                }
                $pluginClass = "\\addons\\$name\\Plugin";
                if (class_exists($pluginClass)) {
                    $hookArr = array_diff((array) get_class_methods($pluginClass), $baseMethods);
                    foreach ($hookArr as $hook) {
                        $manifest['hooks'][$hook][] = $name;
                    }
                }
            }

            $manifest['addon_list'][] = $name;

            // 收集插件自定义路由文件
            $routeDir = $addonDir . 'route' . DIRECTORY_SEPARATOR;
            if (is_dir($routeDir)) {
                foreach (glob($routeDir . '*.php') as $routeFile) {
                    $manifest['routeFiles'][] = $routeFile;
                }
            }

            // 收集插件语言包
            $langDir = $addonDir . 'lang' . DIRECTORY_SEPARATOR;
            if (is_dir($langDir)) {
                foreach (glob($langDir . '*.php') as $langFile) {
                    $manifest['langFiles'][] = $langFile;
                }
            }

            // 挂载插件服务
            $serviceFile = $addonDir . 'service.ini';
            if (is_file($serviceFile)) {
                $info = parse_ini_file($serviceFile, true, INI_SCANNER_TYPED) ?: [];
                $manifest['bind'] = array_merge($manifest['bind'], $info);
            }

            // 导入插件命令
            $commandFile = $addonDir . 'command.php';
            if (is_file($commandFile)) {
                $commands = include_once $commandFile;
                if (is_array($commands)) {
                    $manifest['commands'] = array_merge($manifest['commands'], $commands);
                }
            }
        }

        return $manifest;
    }

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