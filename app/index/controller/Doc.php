<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-27 11:17:57
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \github\TaoLer\app\index\controller\Doc.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use app\facade\Article;
use app\common\model\User;
use app\common\model\Cate;
use app\common\model\Comment;
use app\common\model\Slider;


class Doc extends BaseController
{	

    public function timeline()
    {
		$types = input('type');
		$slider = new Slider();
		//幻灯
		$sliders = $slider->getSliderList(1);
		
		//更新日志
		$timeline = Db::name('time_line')->where('delete_time',0)->order('create_time','desc')->select();
		
		//热议文章
		$artHot = Article::getArtHot(10);

		//首页赞助
		$ad_index = $slider->getSliderList(3);
		
		//首页右栏
		$ad_comm = $slider->getSliderList(2);
		
		//温馨通道
        $fast_links = $slider->getSliderList(8);
		
		//友情链接
		$friend_links = $slider->getSliderList(6);
		
		$assgin = [
			'slider'	=>	$sliders,
			'timeline'	=>	$timeline,
			'artHot'	=>	$artHot,
			'type'		=>	$types,
			'ad_index'	=>	$ad_index,
			'ad_comm'	=>	$ad_comm,
			'fastlinks' =>	$fast_links,
			'flinks'	=>	$friend_links,
			'jspage'	=>	'',
		];
		View::assign($assgin);

		return View::fetch();
    }

}
