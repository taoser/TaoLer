<?php
declare (strict_types = 1);

namespace app\common\controller;

use think\App;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;
use think\facade\Cache;
use app\BaseController as BaseCtrl;

/**
 * 控制器基础类
 */
class BaseController extends BaseCtrl
{
    // 初始化
    protected function initialize()
    {
        $this->uid = Session::get('user_id');
		//系统配置
		$this->showSystem();
        //显示分类导航
        $this->showNav();
		$this->showUser($this->uid);

	}

	//判断是否已登录？
	protected function isLogged()
	{
		if(Session::has('user_id')){
			$this->success('您已登录','/index/index/index');
		}
	}

    //判断是否需要登录？
    protected function isLogin()
    {
        if(!Session::has('user_id')){
            $this->error('请登录','/index/user/login');
        }
    }
	
/*	 //判断密码找回是否已进行了邮件发送？
    protected function isMailed()
    {
        if(Cache::get('repass') != 1){
            $this->error('错误请求，请正确操作！','/index/user/forget');
        }
    }*/

//    显示导航
    protected function showNav()
    {
        //1.查询分类表获取所有分类
		$cateList = Db::name('cate')->where(['status'=>1,'delete_time'=>0])->order('sort','asc')->cache('catename',3600)->select();
		
        //2.将catelist变量赋给模板 公共模板nav.html
        View::assign('cateList',$cateList);

    }
	
	//显示当前登录用户
    protected function showUser($id)
    {
		$user = Cache::get('user'.$id);
		if(!$user){
			//1.查询用户
			$user = Db::name('user')->field('id,name,nickname,user_img,sex,area_id,auth,city,email,sign,point,vip,create_time')->find($id);
			Cache::tag('user')->set('user'.$id,$user,600);
		}
        
		//2.将User变量赋给模板 公共模板nav.html
		View::assign('user',$user);
		return $user;
    }
	
	 //显示网站设置
    protected function showSystem()
    {
        //1.查询分类表获取所有分类
		$sysInfo = Db::name('system')->cache('system',3600)->find(1);
		//头部链接
		$head_links = Cache::get('headlinks');
		if(!$head_links){
			$head_links = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>10])->whereTime('slid_over','>=',time())->field('slid_name,slid_img,slid_href')->select();
			Cache::set('headlinks',$head_links,3600);
		}
		//页脚链接
		$foot_links = Cache::get('footlinks');
		if(!$foot_links){
			$foot_links = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>11])->whereTime('slid_over','>=',time())->field('slid_name,slid_href')->select();
			Cache::set('footlinks',$foot_links,3600);
		}
        View::assign(['sysInfo'=>$sysInfo,'headlinks'=>$head_links,'footlinks'=>$foot_links]);
    }

}
