<?php

namespace think\worker\resetters;

use think\App;
use think\worker\contract\ResetterInterface;
use think\worker\Sandbox;

class ResetConfig implements ResetterInterface
{

    public function handle(App $app, Sandbox $sandbox)
    {
        $app->instance('config', clone $sandbox->getConfig());

        return $app;
    }
}
