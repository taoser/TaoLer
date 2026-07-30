<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\worker\command;

use think\console\Command;
use think\worker\Manager;

/**
 * Worker Server 命令行类
 */
class Server extends Command
{
    protected $config = [];

    public function configure()
    {
        $this->setName('worker')
            ->setDescription('Workerman Server for ThinkPHP');
    }

    public function handle(Manager $manager)
    {
        $manager->start();
    }

}
