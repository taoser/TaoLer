<?php
namespace app\admin\controller\addon;

use app\common\controller\AdminController;
use think\facade\View;

class Template extends AdminController
{

    public function index()
    {
        //
        return View::fetch();
    }
}