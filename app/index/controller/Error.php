<?php
namespace app\index\controller;

use think\facade\View;

class Error
{
    public function __call($method, $args)
    {
		return View::fetch('../view/404');
    }
}