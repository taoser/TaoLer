<?php 
namespace app\facade;

use think\Facade;

/**
 ** @see \app\index\model\Comment
 * @package think\facade
 * @mixin \app\index\model\Comment
 * @method static Json add() 添加评论
 */
class Comment extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\index\entity\Comment';
	}



}