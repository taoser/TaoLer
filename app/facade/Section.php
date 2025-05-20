<?php 
namespace app\facade;

use think\Facade;

/**
 * Class Article
 * @package app\facade
 * @method static array getSlide(int $num) 置顶文章
 */
class Section extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\common\entity\Section';
	}

}