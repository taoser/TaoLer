<?php

$app = new \think\App();
$app->config->set([
    'default' => 'file',
    'stores'  => [
        'file' => [
            'type' => 'File',
        ],
    ],
], 'cache');

$app->initialize();
