<?php 
namespace app\facade;

use think\Facade;

/**
 ** @see \app\model\Comment
 * @package think\facade
 * @method static Json add() 添加评论
 */
class Comment extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\entity\Comment';
	}



}