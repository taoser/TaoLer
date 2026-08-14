<?php

namespace think\worker\concerns;

use think\worker\Conduit;

trait InteractsWithConduit
{
    /** @var Conduit */
    protected $conduit;

    protected function prepareConduit()
    {
        // Workerman on Windows does not support multiple workers in one PHP file.
        // The conduit is Unix-oriented IPC and must be skipped on Windows to keep
        // the process to a single worker instance.
        if (DIRECTORY_SEPARATOR === '\\') {
            return;
        }

        $this->conduit = $this->container->make(Conduit::class);
        $this->conduit->prepare();
        $this->onEvent('workerStart', function () {
            $this->app->instance(Conduit::class, $this->conduit);
        });
    }
}
