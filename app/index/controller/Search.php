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

use think\facade\View;
use think\facade\Request;
use app\facade\Article;

class Search extends IndexBaseController
{
    //搜索功能
	public function getSearch()
	{
        // $page = Request::param('page/d', 1);
        $keywords = Request::param('keywords');
		$artList = Article::getSearchKeyWord($keywords);

        $counts = $artList->count();

        View::assign([
            'artList' => $artList,
            'keywords' => $keywords,
            'counts' => $counts
        ]);

		return View::fetch('search');
	}
}