<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\addon\Template.php
 * @Description: Template
 * @LastEditTime: 2023-03-14 16:52:56
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\addon;

use app\admin\controller\AdminBaseController;
use think\facade\View;

class Template extends AdminBaseController
{

    public function index()
    {
        return View::fetch();
    }
}