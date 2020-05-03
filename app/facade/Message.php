<?php 
namespace app\facade;

use think\Facade;

class Message extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\api\controller\Message';
	}



}