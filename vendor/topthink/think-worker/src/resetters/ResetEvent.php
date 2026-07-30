<?php

namespace think\worker\resetters;

use think\App;
use think\worker\concerns\ModifyProperty;
use think\worker\contract\ResetterInterface;
use think\worker\Sandbox;

class ResetEvent implements ResetterInterface
{
    use ModifyProperty;

    public function handle(App $app, Sandbox $sandbox)
    {
        $event = clone $sandbox->getEvent();
        $this->modifyProperty($event, $app);
        $app->instance('event', $event);

        return $app;
    }
}
