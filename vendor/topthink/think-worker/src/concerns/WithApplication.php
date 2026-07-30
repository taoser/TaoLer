<?php

namespace think\worker\concerns;

use Closure;
use think\App;
use think\worker\App as WorkerApp;
use think\worker\Manager;
use think\worker\Sandbox;
use Throwable;

/**
 * Trait WithApplication
 * @property App $container
 */
trait WithApplication
{
    /**
     * @var WorkerApp
     */
    protected $app;

    protected function prepareApplication()
    {
        if (!$this->app instanceof WorkerApp) {
            $this->app = new WorkerApp($this->container->getRootPath());

            $this->app->bind(WorkerApp::class, App::class);
            $this->app->bind(Manager::class, $this);

            $this->app->initialize();
            $this->app->instance('request', $this->container->request);
            $this->prepareConcretes();
        }
    }

    /**
     * 预加载
     */
    protected function prepareConcretes()
    {
        $defaultConcretes = ['db', 'cache', 'event'];

        $concretes = array_merge($defaultConcretes, $this->getConfig('concretes', []));

        foreach ($concretes as $concrete) {
            $this->app->make($concrete);
        }
    }

    public function getApp()
    {
        return $this->app;
    }

    /**
     * 获取沙箱
     * @return Sandbox
     */
    protected function getSandbox()
    {
        return $this->app->make(Sandbox::class);
    }

    /**
     * 在沙箱中执行
     * @param Closure $callable
     */
    public function runInSandbox(Closure $callable, ?object $key = null)
    {
        try {
            $this->getSandbox()->run($callable, $key);
        } catch (Throwable $e) {
            $this->logServerError($e);
        }
    }
}
