<?php

namespace think\worker;

use Closure;
use InvalidArgumentException;
use ReflectionObject;
use RuntimeException;
use think\Config;
use think\Container;
use think\Event;
use think\exception\Handle;
use think\worker\App;
use think\worker\concerns\ModifyProperty;
use think\worker\contract\ResetterInterface;
use think\worker\resetters\ClearInstances;
use think\worker\resetters\ResetConfig;
use think\worker\resetters\ResetEvent;
use think\worker\resetters\ResetModel;
use think\worker\resetters\ResetPaginator;
use think\worker\resetters\ResetService;
use Throwable;
use WeakMap;

class Sandbox
{
    use ModifyProperty;

    /** @var App|null */
    protected $snapshot;

    /** @var WeakMap */
    protected $snapshots;

    /** @var App */
    protected $app;

    /** @var Config */
    protected $config;

    /** @var Event */
    protected $event;

    /** @var ResetterInterface[] */
    protected $resetters = [];
    protected $services  = [];

    public function __construct(App $app)
    {
        $this->app       = $app;
        $this->snapshots = new WeakMap();
        $this->initialize();
    }

    protected function initialize()
    {
        Container::setInstance(function () {
            return $this->getSnapshot();
        });

        $this->setInitialConfig();
        $this->setInitialServices();
        $this->setInitialEvent();
        $this->setInitialResetters();
    }

    public function run(Closure $callable, ?object $key = null)
    {
        $this->snapshot = $this->createApp($key);
        try {
            $this->snapshot->invoke($callable, [$this]);
        } catch (Throwable $e) {
            $this->snapshot->make(Handle::class)->report($e);
        } finally {
            if (empty($key)) {
                $this->snapshot->clearInstances();
            }
            $this->snapshot = null;
            $this->setInstance($this->app);
        }
    }

    protected function createApp(?object $key = null)
    {
        if (!empty($key)) {
            if (isset($this->snapshots[$key])) {
                return $this->snapshots[$key]->app;
            }
        }

        $app = clone $this->app;
        $this->setInstance($app);
        $this->resetApp($app);

        if (!empty($key)) {
            $this->snapshots[$key] = new class($app) {
                public function __construct(public App $app)
                {
                }

                public function __destruct()
                {
                    $this->app->clearInstances();
                }
            };
        }

        return $app;
    }

    protected function resetApp(App $app)
    {
        foreach ($this->resetters as $resetter) {
            $resetter->handle($app, $this);
        }
    }

    protected function setInstance(App $app)
    {
        $app->instance('app', $app);
        $app->instance(Container::class, $app);

        $reflectObject   = new ReflectionObject($app);
        $reflectProperty = $reflectObject->getProperty('services');
        $services        = $reflectProperty->getValue($app);

        foreach ($services as $service) {
            $this->modifyProperty($service, $app);
        }
    }

    /**
     * Set initial config.
     */
    protected function setInitialConfig()
    {
        $this->config = clone $this->app->config;
    }

    protected function setInitialEvent()
    {
        $this->event = clone $this->app->event;
    }

    protected function setInitialServices()
    {
        $services = $this->config->get('worker.services', []);

        foreach ($services as $service) {
            if (class_exists($service) && !in_array($service, $this->services)) {
                $serviceObj               = new $service($this->app);
                $this->services[$service] = $serviceObj;
            }
        }
    }

    /**
     * Initialize resetters.
     */
    protected function setInitialResetters()
    {
        $resetters = [
            ClearInstances::class,
            ResetConfig::class,
            ResetEvent::class,
            ResetService::class,
            ResetModel::class,
            ResetPaginator::class,
        ];

        $resetters = array_merge($resetters, $this->config->get('worker.resetters', []));

        foreach ($resetters as $resetter) {
            $resetterClass = $this->app->make($resetter);
            if (!$resetterClass instanceof ResetterInterface) {
                throw new RuntimeException("{$resetter} must implement " . ResetterInterface::class);
            }
            $this->resetters[$resetter] = $resetterClass;
        }
    }

    public function getSnapshot()
    {
        $snapshot = $this->snapshot;
        if ($snapshot instanceof App) {
            return $snapshot;
        }

        throw new InvalidArgumentException('The app object has not been initialized');
    }

    /**
     * Get config snapshot.
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getServices()
    {
        return $this->services;
    }

}
