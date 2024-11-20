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

use app\controller\index\IndexBaseController;
use think\facade\View;
use think\facade\Request;
use app\facade\Article;

class Search extends IndexBaseController
{
    //搜索功能
	public function getSearch()
	{
        $ser = Request::only(['keywords']);
		$artList = Article::getSearchKeyWord($ser['keywords']);
        $counts = $artList->count();

        $searchs = [
            'artList' => $artList,
            'keywords' => $ser['keywords'],
            'counts' => $counts
        ];
        View::assign($searchs);
		return View::fetch('search');
	}
}