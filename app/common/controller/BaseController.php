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
use app\BaseController as BaseCtrl;

/**
 * 控制器基础类
 */
class BaseController extends BaseCtrl
{

	/**
	 * 登录用户uid
	 *
	 * @var int|null
	 */
	protected $uid;

	/**
	 * 登录用户信息
	 *
	 * @var array|object
	 */
	protected $user;

    /**
	 * 初始化系统，导航，用户
	 */
    protected function initialize()
    {
        $this->setUid();
		//系统配置
		$this->showSystem();

		//变量赋给模板
		View::assign([
			//显示子分类导航
			'subcatelist'	=> $this->showSubnav(),
			//当前登录用户
			'user'			=> $this->showUser($this->uid),
		]);

	}

	// 用户登录
	private function setUid()
	{
		if(Session::has('user_id')) {
			$this->uid = Session::get('user_id') ?? -1;
		}
	}

	private function setUser()
	{
		if(Session::has('user_id')) {
			$this->uid = Session::get('user_id') ?? -1;
		}
	}

	// 显示子导航subnav
    protected function showSubnav()
    {
		$subCateList = []; // 没有点击任何分类，点击首页获取全部分类信息
        //1.查询父分类id
		$pCate = Db::name('cate')->field('id,pid,ename,catename,is_hot')->where(['ename'=>input('ename'),'status'=>1,'delete_time'=>0])->find();

		if(!is_null($pCate)) { // 点击分类，获取子分类信息
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
		if($id == -1) return [];
		//1.查询用户
		$user = Db::name('user')
		->alias('u')
		->join('user_viprule v','v.vip = u.vip')
		->field('u.id as id,v.id as vid,name,nickname,user_img,sex,area_id,auth,city,phone,email,active,sign,point,u.vip as vip,nick,u.create_time as create_time')
		->cache('user'.$id)
		->find((int)$id);
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
