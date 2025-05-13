<?php 
namespace app\facade;

use think\Facade;

/**
 * Class Article
 * @package app\facade
 * @method static array add(array $data) 添加文章
 * @method static bool edit(array $data) 编辑文章
 * @method static bool remove(array $ids) 多选删除
 * @method static array getTopss(int $num) 置顶文章
 * @method static array getIndexs(int $num) 首页文章
 * @method static array getHots(int $num) 热点
 */
class AdSlide extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\common\entity\AdSlide';
	}

}