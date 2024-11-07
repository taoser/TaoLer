<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Request;
use think\facade\View;


class Active extends BaseController
{	

	//api首页
	public function index()
	{
		View::assign(['jspage'=>'']);
		return View::fetch('index');
    }

	//api首页
	public function email()
	{
		if(Request::isPost()){
			$url = Request::param('url');
			$atime = substr($url,0,10);
			$mde = substr($url,10,32);
			$uid = substr($url,42);
			$t = time() - $atime;
			$mins = floor(($t%3600)/60);
			if($mins > 10){
				return json(['code'=>-1,'msg'=>'已超时']);
			}
			$user = Db::name('user')->find($uid);
			$umail = md5($user['email']);
			if($umail == $mde){
				$result = Db::name('user')->update(['id'=>$uid,'active'=>1]);
				if($result){
					Cache::delete('user'.$this->uid);
					return json(['code'=>0,'msg'=>'激活成功','url'=>(string) url('login/index')]);
				}else{
					return json(['code'=>-1,'msg'=>'激活失败！']);
				}
			} else {
				return json(['code'=>-1,'msg'=>'请求错误！']);
			}
		}

    }

}