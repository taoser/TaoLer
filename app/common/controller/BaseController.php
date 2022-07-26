<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-26 15:17:08
 * @LastEditors: TaoLer
 * @Description: 前端基础控制器设置
 * @FilePath: \github\TaoLer\app\common\controller\BaseController.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
declare (strict_types = 1);

namespace app\common\controller;

use think\facade\Request;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;
use think\facade\Cache;
use app\facade\Article;
use app\BaseController as BaseCtrl;

/**
 * 控制器基础类
 */
class BaseController extends BaseCtrl
{
    /**
	 * 初始化系统，导航，用户
	 */
    protected function initialize()
    {
        $this->uid = Session::get('user_id');
		//系统配置
		$this->showSystem();
        //显示分类导航
        $this->showNav();
		//用户
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

	// 显示导航
    protected function showNav()
    {
        //1.查询分类表获取所有分类
		$cateList = Db::name('cate')->where(['status'=>1,'delete_time'=>0])->order('sort','asc')->cache('catename',3600)->select();
		
        //2.将catelist变量赋给模板 公共模板nav.html
        View::assign('cateList',$cateList);
		return $cateList;

    }
	
	//显示当前登录用户
    protected function showUser($id)
    {
		$user = Cache::get('user'.$id);
		if(!$user){
			//1.查询用户
			$user = Db::name('user')->field('id,name,nickname,user_img,sex,area_id,auth,city,email,active,sign,point,vip,create_time')->find($id);
			Cache::tag('user')->set('user'.$id,$user,600);
		}
        
		//2.将User变量赋给模板 公共模板nav.html
		View::assign('user',$user);
		return $user;
    }

	//热门标签
	protected function getHotTag()
	{
		//热门标签
		return Article::getHotTags();
        //转换为字符串
		// $tagStr = implode(",",$tags);
		//转换为数组并去重
		// return array_unique(explode(",",$tagStr));
	}
	
	//显示网站设置
    protected function showSystem()
    {
        //1.查询分类表获取所有分类
		$sysInfo = $this->getSystem();
		$slider = new \app\common\model\Slider();
		//头部链接
		$head_links = $slider->getSliderList(10);
		//页脚链接
		$foot_links = $slider->getSliderList(11);
		//友情链接
		$friend_links = $slider->getSliderList(9);
		//获取热门标签
		$hotTag = $this->getHotTag();

		$assign = [
			'sysInfo'	=> $sysInfo,
			'headlinks'	=> $head_links,
			'footlinks'	=> $foot_links,
			'flinks'	=> $friend_links,
			'hotTag'	=> $hotTag,
			'host'		=> Request::domain() . '/'
		];
		
        View::assign($assign);
		return $sysInfo;
    }

}
