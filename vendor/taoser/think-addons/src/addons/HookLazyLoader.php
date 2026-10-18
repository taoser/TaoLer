<?php
declare(strict_types=1);

namespace taoser\addons;

use think\facade\Event;
use think\facade\Cache;
use think\facade\Config;

class HookLazyLoader
{
    private static ?HookLazyLoader $instance = null;
    private array $hookRegistry = [];
    private array $instances = [];
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
        $this->initialized = true;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function registerHook(string $hookName, array $listeners): void
    {
        $this->hookRegistry[$hookName] = $listeners;

        Event::listen($hookName, function (&$params) use ($hookName) {
            return $this->executeHook($hookName, $params);
        });
    }

    public function executeHook(string $hookName, &$params)
    {
        if (isset($this->executingHooks[$hookName])) {
            return [];
        }

        $this->executingHooks[$hookName] = true;

        $results = [];
        $listeners = $this->hookRegistry[$hookName] ?? [];

        try {
            foreach ($listeners as $listener) {
                [$class, $method] = $listener;

                if (!class_exists($class)) {
                    continue;
                }

                try {
                    $instance = $this->instances[$class] ?? null;
                    if ($instance === null) {
                        $parts     = explode('\\', ltrim($class, '\\'));
                        $addonName = $parts[1] ?? '';
                        $instance = $addonName !== '' ? \get_addons_instance($addonName) : null;
                        if ($instance === null) {
                            $instance = app()->make($class);
                        }
                        $this->instances[$class] = $instance;
                    }
                    if (method_exists($instance, 'setRequest')) {
                        $instance->setRequest(app()->request);
                    }

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
        } finally {
            unset($this->executingHooks[$hookName]);
        }

        return $results;
    }

    public function reset(): void
    {
        $this->instances = [];
        $this->initialized = false;
        $this->executingHooks = [];
    }

    public function resetForNewRequest(): void
    {
        $this->executingHooks = [];
        foreach ($this->instances as $instance) {
            if (method_exists($instance, 'setRequest')) {
                $instance->setRequest(app()->request);
            }
        }
    }

    public function getRegistry(): array
    {
        return $this->hookRegistry;
    }
}