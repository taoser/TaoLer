<?php

namespace think\worker\resetters;

use think\App;
use think\worker\contract\ResetterInterface;
use think\worker\Sandbox;

class ClearInstances implements ResetterInterface
{
    public function handle(App $app, Sandbox $sandbox)
    {
        $instances = ['log', 'session', 'view', 'response', 'cookie'];

        $instances = array_merge($instances, $sandbox->getConfig()->get('worker.instances', []));

        foreach ($instances as $instance) {
            $app->delete($instance);
        }

        return $app;
    }
}
