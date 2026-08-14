<?php

namespace think\worker\concerns;

use think\App;
use think\worker\Ipc;
use think\worker\Watcher;
use think\worker\Worker;
use Workerman\Redis\Client;

/**
 * Trait InteractsWithServer
 * @property App $container
 */
trait InteractsWithServer
{
    /** @var Ipc */
    protected $ipc;

    protected $workerId = null;
    protected $stopping = false;

    public function addWorker(callable $func, $name = 'none', int $count = 1): Worker
    {
        $worker = new Worker();

        $worker->name  = $name;
        $worker->count = $count;

        $worker->onWorkerStart = function (Worker $worker) use ($func) {
            $this->clearCache();
            $this->prepareApplication();

            if (DIRECTORY_SEPARATOR !== '\\') {
                $this->conduit->connect();
                $this->workerId = $this->ipc->listenMessage();
            }

            $this->triggerEvent('workerStart', $worker);

            $func($worker);
        };

        $worker->onWorkerReload = function () {
            $this->stopping = true;
        };

        return $worker;
    }

    public function getWorkerId()
    {
        return $this->workerId;
    }

    public function isStopping()
    {
        return $this->stopping;
    }

    /**
     * 启动服务
     */
    public function start(): void
    {
        $this->initialize();
        $this->prepareIpc();
        $this->triggerEvent('init');

        // 热更新依赖 Unix 信号，Windows 下只能保留单个 worker，跳过额外热更新 worker
        if (DIRECTORY_SEPARATOR !== '\\' && $this->getConfig('hot_update.enable', false)) {
            $this->addHotUpdateWorker();
        }

        Worker::runAll();
    }

    protected function prepareIpc()
    {
        $this->ipc = $this->container->make(Ipc::class);
    }

    public function sendMessage($workerId, $message)
    {
        $this->ipc->sendMessage($workerId, $message);
    }

    /**
     * 热更新
     */
    protected function addHotUpdateWorker()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return;
        }

        $worker = new Worker();

        $worker->name       = 'hot update';
        $worker->reloadable = false;

        $worker->onWorkerStart = function () {
            $watcher = $this->container->make(Watcher::class);
            $watcher->watch(function () {
                // Windows 兼容处理
                if (DIRECTORY_SEPARATOR === '\\') {
                    // Windows 下使用 Worker::reloadWorkers() 方法
                    if (method_exists('\Workerman\Worker', 'reloadWorkers')) {
                        \Workerman\Worker::reloadWorkers();
                    }
                } else {
                    posix_kill(posix_getppid(), SIGUSR1);
                }
            });
        };
    }

    /**
     * 清除apc、op缓存
     */
    protected function clearCache()
    {
        if (extension_loaded('apc')) {
            apc_clear_cache();
        }

        if (extension_loaded('Zend OPcache')) {
            opcache_reset();
        }
    }

}
