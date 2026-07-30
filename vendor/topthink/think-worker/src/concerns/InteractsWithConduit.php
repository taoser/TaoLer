<?php

namespace think\worker\concerns;

use think\worker\Conduit;

trait InteractsWithConduit
{
    /** @var Conduit */
    protected $conduit;

    protected function prepareConduit()
    {
        $this->conduit = $this->container->make(Conduit::class);
        $this->conduit->prepare();
        $this->onEvent('workerStart', function () {
            $this->app->instance(Conduit::class, $this->conduit);
        });
    }
}
