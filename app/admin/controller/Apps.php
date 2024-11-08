<?php

/**
 * @Program: TaoLer 2023/3/11
 * @FilePath: app\admin\controller\Apps.php
 * @Description: Apps.php
 * @LastEditTime: 2023-03-11 10:03:30
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Session;
use think\facade\Request;



class Apps extends AdminController
{
    /**
     * App应用服务
     * @return \think\response\Redirect
     */
    public function index()
	{
        Session::set('ruleTable','auth_rule_server');
		return redirect((string) url('index/index'));
    }
	
	public function delete()
	{
		Session::delete('ruleTable');
		return redirect((string) url('index/index'));
	}

}