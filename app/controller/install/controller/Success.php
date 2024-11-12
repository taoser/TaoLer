<?php
namespace app\install\controller;

use app\common\controller\BaseController;
use think\facade\View;

class Success extends BaseController
{
	// 安装成功页面
	public function complete(){
		return View::fetch('complete');
	}

}