<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = new \think\App();
$app->initialize();

echo "=== entry_prefix config ===\n";
var_dump(\think\facade\Config::get('addons.entry_prefix'));

echo "=== manifest routePrefixes ===\n";
$system = $app->addons;
$ref = new \ReflectionMethod($system, 'getManifest');
$manifest = $ref->invoke($system);
var_dump(array_keys($manifest['routePrefixes']));
