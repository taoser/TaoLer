<?php
declare(strict_types=1);

namespace taoser\addons\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

/**
 * 插件清单预热：把全量扫描结果转储为 PHP 文件（runtime/addons/manifest.php），
 * 由 opcache 承载。生产环境部署/插件安装后执行：
 *   php think addons:warm
 * 执行后每请求零目录扫描、零缓存反序列化。
 */
class WarmManifest extends Command
{
    protected function configure(): void
    {
        $this->setName('addons:warm')
            ->setDescription('Dump addons manifest to a PHP file (opcache friendly)');
    }

    protected function execute(Input $input, Output $output): int
    {
        /** @var \taoser\addons\AddonsSystem $system */
        $system = $this->app->addons;
        $system->invalidateManifest();
        $file = $system->dumpManifest();

        $output->writeln('<info>addons manifest warmed:</info> ' . $file);

        return 0;
    }
}
