<?php

use Symfony\Component\VarDumper\VarDumper;
use think\dumper\Dumper;

VarDumper::setHandler(function ($var, ?string $label = null) {
    app(Dumper::class)->dump($var, $label);
});
