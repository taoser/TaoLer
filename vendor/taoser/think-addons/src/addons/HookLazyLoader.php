<?php
declare(strict_types=1);

namespace taoser\addons;

use think\facade\Event;
use think\facade\Cache;

class HookLazyLoader
{
    private static ?HookLazyLoader $instance = null;
    private array $hookRegistry = [];
    private array $loadedHooks = [];
    private array $highFrequencyHooks = [];
    private bool $initialized = false;
    private array $executingHooks = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function initialize(array $hooks): void
    {
        if ($this->initialized) {
            return;
        }

        $this->hookRegistry = $hooks;
        $this->loadHighFrequencyHooks();
        $this->initialized = true;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function registerHook(string $hookName, array $listeners): void
    {
        $this->hookRegistry[$hookName] = $listeners;

        Event::listen($hookName, function ($params) use ($hookName) {
            return $this->executeHook($hookName, $params);
        });
    }

    public function executeHook(string $hookName, $params)
    {
        if (isset($this->executingHooks[$hookName])) {
            return [];
        }

        $this->executingHooks[$hookName] = true;

        if (!isset($this->loadedHooks[$hookName])) {
            $this->loadHook($hookName);
        }

        unset($this->executingHooks[$hookName]);

        $results = [];
        $listeners = $this->hookRegistry[$hookName] ?? [];
        
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

        $this->loadedHooks[$hookName] = true;
        
        return $results;
    }

    private function loadHook(string $hookName): void
    {
        if (!isset($this->hookRegistry[$hookName])) {
            return;
        }

        $this->loadedHooks[$hookName] = true;
        $this->trackHookUsage($hookName);
    }

    private function loadHighFrequencyHooks(): void
    {
        $this->highFrequencyHooks = Cache::get('high_frequency_hooks', []);
        
        foreach ($this->highFrequencyHooks as $hookName) {
            if (isset($this->hookRegistry[$hookName])) {
                $this->loadedHooks[$hookName] = true;
            }
        }
    }

    private function trackHookUsage(string $hookName): void
    {
        if (!app()->isDebug()) {
            $usage = Cache::get('hook_usage_stats', []);
            $usage[$hookName] = ($usage[$hookName] ?? 0) + 1;
            
            if (count($usage) > 100) {
                $this->optimizeHighFrequencyHooks($usage);
            }
            
            Cache::set('hook_usage_stats', $usage, 86400);
        }
    }

    private function optimizeHighFrequencyHooks(array $usage): void
    {
        arsort($usage);
        $topHooks = array_slice(array_keys($usage), 0, 10);
        
        Cache::set('high_frequency_hooks', $topHooks, 86400);
        
        Cache::delete('hook_usage_stats');
    }

    public function loadHooksForModule(string $module): void
    {
        $moduleHooks = Cache::get("module_hooks_{$module}", []);
        
        foreach ($moduleHooks as $hookName) {
            if (isset($this->hookRegistry[$hookName]) && !isset($this->loadedHooks[$hookName])) {
                $this->loadedHooks[$hookName] = true;
            }
        }
    }

    public function reset(): void
    {
        $this->loadedHooks = [];
        $this->initialized = false;
        $this->executingHooks = [];
    }

    public function getLoadedHooks(): array
    {
        return array_keys($this->loadedHooks);
    }

    public function getRegistry(): array
    {
        return $this->hookRegistry;
    }
}