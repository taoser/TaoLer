<?php

namespace think\worker\resetters;

use think\App;
use think\Model;
use think\worker\contract\ResetterInterface;
use think\worker\Sandbox;

class ResetModel implements ResetterInterface
{

    public function handle(App $app, Sandbox $sandbox)
    {
        if (class_exists(Model::class)) {
            Model::setInvoker(function (...$args) use ($sandbox) {
                return $sandbox->getSnapshot()->invoke(...$args);
            });
        }
    }
}
