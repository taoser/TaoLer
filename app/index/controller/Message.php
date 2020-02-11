<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\Session;
use think\facade\Request;
use think\Db;

class Message extends BaseController
{
	//消息
	public function nums(){

            //$res['status'] = 0;
			$res=['status' =>0,'count' => 0, 'msg' => 'nums'];
        
        return $res;
	}
	
}