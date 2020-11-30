<?php 
namespace app\facade;

use think\Facade;

class User extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\common\model\User';
	}



}