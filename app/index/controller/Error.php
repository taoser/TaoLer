<?php
namespace app\index\controller;

use think\facade\View;

class Error extends IndexBaseController
{
    public function __call($method, $args)
    {
		View::assign('jspage','');
		return View::fetch('../../404');
    }
}