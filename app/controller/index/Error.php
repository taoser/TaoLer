<?php
namespace app\index\controller;

use think\facade\View;
use app\common\controller\BaseController;

class Error extends BaseController
{
    public function __call($method, $args)
    {
		View::assign('jspage','');
		return View::fetch('../../404');
    }
}