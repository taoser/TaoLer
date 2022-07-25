<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-20 13:21:54
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \TaoLer\app\index\controller\Search.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */

namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use app\facade\Article;
use app\common\model\Slider;

class Search extends BaseController
{
    //搜索功能
	public function getSearch()
	{
        $ser = Request::only(['keywords']);
		$artList = Article::getSearchKeyWord($ser['keywords']);
        $counts = $artList->count();
        $slider = new Slider();
        //首页右栏
        $ad_comm = $slider->getSliderList(2);
        //	查询热议
        $artHot = Article::getArtHot(10);

        $searchs = [
            'artList' => $artList,
            'keywords' => $ser['keywords'],
            'counts' => $counts,
            'ad_comm'=>$ad_comm,
            'artHot'=>$artHot,
            'jspage'=>''
        ];
        View::assign($searchs);
		return View::fetch('search');
	}
}