<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cache;
use think\facade\Db;



class Jump extends BaseController
{
	
	//用户中心
	public function index()
	{
		$u = Request::param();
		//查询用户
        $user = Db::name('user')->whereOr('name',$u['name'])->whereOr('nickname',$u['name'])->find();
		$id = $user['id'];
		return redirect((string) url('user/home',['id'=>$id]));
    }
    

}