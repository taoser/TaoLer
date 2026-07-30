<?php

namespace think\worker\contract;

use think\App;
use think\worker\Sandbox;

interface ResetterInterface
{
    /**
     * "handle" function for resetting app.
     *
     * @param \think\App $app
     * @param Sandbox $sandbox
     */
    public function handle(App $app, Sandbox $sandbox);
}
