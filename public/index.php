<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\App;

// [ 应用入口文件 ]

// $tao_config = __DIR__ ."/../config/taoler.php";

// if(file_exists($tao_config)) {
//     $conf = (array) include $tao_config;
//     if(isset($conf['config']['static_html']) && $conf['config']['static_html'] === 1) {
//         // 静态文件存放目录
//         $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//         $staticPath = str_replace('\\','/', './static_html'. $path);
//         // 首页静态文件路径
//         $indexPath = $staticPath.'index.html';
//         if(file_exists($indexPath)) {
//             return include $indexPath;
//         }
//         // 其它页面静态文件路径
//         if(file_exists($staticPath) && is_file($staticPath)) {
//             return include $staticPath;
//         }

//     }
// }

require __DIR__ . '/../vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);




