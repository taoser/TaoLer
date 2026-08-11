<?php
namespace app\index\controller;

use think\facade\View;

class Error extends IndexBaseController
{
    public function __call($method, $args)
    {
		return response('404 Not Found', 404);
    }
}