<?php 
namespace app\facade;

use think\Facade;

class Comment extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\index\model\Comment';
	}



}