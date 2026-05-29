<?php

use app\AppService;
use taoser\addons\Service as AddonsService;

// 系统服务定义文件
// 服务在完成全局初始化之后执行
return [
    AppService::class,
    AddonsService::class,
];
