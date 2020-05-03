<?php 
namespace app\facade;

use think\Facade;

class Jump extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\api\controller\Jump';
	}

}