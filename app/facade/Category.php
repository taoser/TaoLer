<?php

namespace app\facade;

use think\Facade;

/**
 * Class Article
 * @package app\facade
 * @method static array getArtTop(int $num) 获取置顶文章
 * @method static array getArtList(int $num) 获取文章列表
 * @method static array getArtHot(int $num) 获取精华文章
 */
class Category extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\index\model\Category';
    }

}