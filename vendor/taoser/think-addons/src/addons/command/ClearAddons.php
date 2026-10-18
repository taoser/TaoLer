<?php

declare(strict_types=1);

namespace taoser\addons\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;

class ClearAddons extends Command
{
    protected function configure()
    {
        $this->setName('addons:clear')
             ->setDescription('Clear addons manifest/hooks/list cache');
    }

    protected function execute(Input $input, Output $output)
    {
        // 清空各插件的 info 缓存（键名带插件名，无法枚举，需按目录逐个删除）
        $addonsPath = root_path() . 'addons' . DIRECTORY_SEPARATOR;
        if (is_dir($addonsPath)) {
            foreach (scandir($addonsPath) as $name) {
                if ($name === '.' || $name === '..') {
                    continue;
                }
                if (is_dir($addonsPath . $name)) {
                    Cache::delete('addon_info_' . $name);
                }
            }
        }

        $keys = [
            'addons_manifest',
            'addonslist',
            'addons_list',
            'addons_config',
            'hooks',
            'hook_usage_stats',
            'high_frequency_hooks',
            'addon_taglib',
        ];
        foreach ($keys as $key) {
            Cache::delete($key);
        }

        $output->writeln('addons cache cleared');

        return 0;
    }
}
