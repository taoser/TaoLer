<?php
/**
 * 临时验证脚本：isAddonRequest() 路径预判断言
 * 验证完成后删除
 */
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = new \think\App();
$app->initialize();

$system = $app->addons;

// 重置 request 的 pathinfo 缓存（反射）
$setPath = function (\think\Request $request, string $path) {
    $prop = new \ReflectionProperty(\think\Request::class, 'pathinfo');
    $prop->setValue($request, $path);
};

$cases = [
    // 非插件请求 => false
    ''                          => false,
    'index/index'               => false,
    'login-status'              => false,
    'article/add'               => false,
    'admin/index/index'         => false,
    'api/jida/index'            => false,
    // 插件请求 => true
    'app/demo/index/index'      => true,
    'apps/idx/signjson'         => true,
    'pay/checkstatus'           => true,
    'pay/weixin/notify'         => true,
    'shop/notify'               => true,
    'taocai/savedata'           => true,
    'addons/huodong/list'       => true,
    'addons/huodongjoin/list'   => true,
];

$allOk = true;
foreach ($cases as $path => $expect) {
    $setPath($app->request, $path);
    $got = $system->isAddonRequest();
    $ok  = $got === $expect;
    $allOk = $allOk && $ok;
    printf("%-26s expect=%-5s got=%-5s %s\n", $path ?: '(root)', var_export($expect, true), var_export($got, true), $ok ? 'OK' : 'FAIL');
}

echo "\n" . ($allOk ? "ALL CASES PASSED" : "SOME CASES FAILED") . "\n";
