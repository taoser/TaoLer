<?php

namespace app\facade;

use think\Facade;

/**
 * Class Article
 * @package app\facade
 * @method static bool add(array $data) 添加栏目
 * @method static bool edit(array $data) 编辑栏目
 * @method static think\response\Json delete(int $id) 删除栏目
 */
class Category extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\index\entity\Category';
    }

}