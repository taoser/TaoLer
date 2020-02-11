<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
use app\admin\model\Admin;
use app\admin\model\Article;

class Index extends AdminController
{
	
	protected function initialize()
    {
        parent::initialize();
       
    }

    public function index()
	{
        return View::fetch('index');
    }
	

    public function set()
	{
        return view();
    }

    public function message(){
        return view();
    }

    public function home(){
		$sys = Db::name('system')->find(1);
		$now = time();
		$count = $now-$sys['create_time'];
		$days = floor($count/86400);
		$hos = floor(($count%86400)/3600);
		$mins = floor(($count%3600)/60);
		View::assign(['sys'=>$sys,'day'=>$days,'hos'=>$hos,'mins'=>$mins]);
        return View::fetch();
    }
	
	
	  public function layout(){
		
        return view();
    }
}