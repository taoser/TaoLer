<?php
namespace think\worker;

use think\worker\watcher\Driver;

/**
 * @mixin Driver
 */
class Watcher extends \think\Manager
{
    protected $namespace = '\\think\\worker\\watcher\\';

    protected function getConfig(string $name, $default = null)
    {
        return $this->app->config->get('worker.hot_update.' . $name, $default);
    }

    protected function resolveParams($name): array
    {
        return [
            array_filter($this->getConfig('include', []), function ($dir) {
                return is_dir($dir);
            }),
            $this->getConfig('exclude', []),
            $this->getConfig('name', []),
        ];
    }

    public function getDefaultDriver()
    {
        // Windows 下默认使用 scan 驱动
        if (DIRECTORY_SEPARATOR === '\\') {
            return 'scan';
        }
        return $this->getConfig('type', 'scan');
    }
}
