<?php 
namespace app\facade;

use think\Facade;

/**
 * Class Article
 * @package app\facade
 * @method static array getLink(int $num) 添加文章
 */
class Link extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\common\entity\Link';
	}

}