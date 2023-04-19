<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-08-15 13:30:05
 * @LastEditors: TaoLer
 * @Description: 前端基础控制器设置
 * @FilePath: \TaoLer\app\common\controller\BaseController.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
declare (strict_types = 1);

namespace app\common\controller;

use think\facade\Request;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;
use think\facade\Cache;
use app\BaseController as BaseCtrl;
use app\common\model\Cate;

/**
 * 控制器基础类
 */
class BaseController extends BaseCtrl
{

	protected $uid = '';

    /**
	 * 初始化系统，导航，用户
	 */
    protected function initialize()
    {
        $this->uid = Session::get('user_id');
		//系统配置
		$this->showSystem();

		//变量赋给模板
		View::assign([
			//显示分类导航
			'cateList'		=> $this->showNav(),
			//显示子分类导航
			'subcatelist'	=> $this->showSubnav(),
			//当前登录用户
			'user'			=> $this->showUser($this->uid),
		]);

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

	// 显示导航nav
    protected function showNav()
    {
        //1.查询分类表获取所有分类
        $cate = new Cate();
        $cateList = $cate->menu();
         $list =  getTree($cateList);
        // 排序
        $cmf_arr = array_column($list, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $list);
        return $list;
    }

	// 显示子导航subnav
    protected function showSubnav()
    {
		// dump($this->showNav());
        //1.查询父分类id
		$pCate = Db::name('cate')->field('id,pid,ename,catename,is_hot')->where(['ename'=>input('ename'),'status'=>1,'delete_time'=>0])->find();
	
		if(empty($pCate)) { // 没有点击任何分类，点击首页获取全部分类信息
			$subCateList = $this->showNav();
		} else { // 点击分类，获取子分类信息
			$parentId = $pCate['id'];
			$subCate = Db::name('cate')->field('id,ename,catename,is_hot,pid')->where(['pid'=>$parentId,'status'=>1,'delete_time'=>0])->select()->toArray();
			if(!empty($subCate)) { // 有子分类
				$subCateList = array2tree($subCate);
			} else { //无子分类
				if($pCate['pid'] == 0) {
					//一级菜单
					$subCateList[] = $pCate;
				} else {
					//子菜单下如果无子菜单，则显示全部兄弟分类
					$parament = Db::name('cate')->field('id,ename,catename,is_hot,pid')->where(['pid'=>$pCate['pid'],'status'=>1,'delete_time'=>0])->order(['sort' => 'asc'])->select()->toArray();
					$subCateList = array2tree($parament);
				}
				
			}
		}
        
		return $subCateList;

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
		return $user;
    }

	//热门标签
	protected function getHotTag()
	{
		//热门标签
		//return Article::getHotTags();
        //转换为字符串
		// $tagStr = implode(",",$tags);
		//转换为数组并去重
		// return array_unique(explode(",",$tagStr));
		$allTags = Db::name('tag')->field('name,ename')->select();
		$tagHot = [];
        foreach($allTags as $v) {
            $tagHot[] = ['name'=>$v['name'],'url'=> (string) url('tag_list',['ename'=>$v['ename']])];
        }
        
        return $tagHot;

	}
	
	//显示网站设置
    protected function showSystem()
    {
        //1.查询分类表获取所有分类
		$sysInfo = $this->getSystem();
		//获取热门标签
		$hotTag = $this->getHotTag();

		$assign = [
			'sysInfo'	=> $sysInfo,
			'hotTag'	=> $hotTag,
			'host'		=> Request::domain() . '/'
		];
		
        View::assign($assign);
		return $sysInfo;
    }

}
