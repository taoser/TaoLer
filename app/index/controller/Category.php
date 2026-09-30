<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-30 22:03:55
 * @LastEditors: TaoLer
 * @Description: 分类控制器
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\index\controller\Category.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\index\controller;

use think\Request;
use think\Response;
use think\facade\View;
use think\facade\Db;
use app\facade\Category as CategoryEntity;
use app\entity\Page as PageEntity;

class Category extends IndexBaseController
{
    public function list(Request $request): string
    {
        global $page;
		//动态参数
		$ename = $request->param('ename', '');
		$flag = $request->param('flag');
		$page = $request->param('page/d', 1);

		// 分类信息
		$categoryInfo = CategoryEntity::getCateInfoByEname($ename);

		// 单页分类
		// type 1列表2单页3链接
		if($ename !== 'all' && $categoryInfo->type == 2) {
			$pageEntity = new PageEntity();
			$single = $pageEntity->getSinglePageDetail($categoryInfo->id);
			
			View::assign('article', $single);
			return View::fetch('category/' . $categoryInfo->tpl . '/single');
		}

		if(empty($flag)) {
			$url = (string) url('category_page', ['ename' => $ename, 'page' => $page]);
		} else {
			$url = (string) url('category_flag_page', ['ename' => $ename, 'flag' => $flag, 'page' => $page]);
		}
		// 当前页url
		
		// 返回最后/前面的字符串
		$path = substr($url, 0, strrpos($url, "/"));
		// 下一页url
		$next = $path . '/' . ++$page . '.html';

		$assignArr = [
			'category'	=> $categoryInfo,
			'path'	=> $path,
			'page'	=> ++$page,
			'next'  => $next
		];

		View::assign($assignArr);

		$categoryView = is_null($categoryInfo) ? 'category/list' : 'category/' . $categoryInfo->tpl . '/list';

        return View::fetch($categoryView);
    }

	/**
	 * 文章可选分类树(排除单页分类)
	 * @return Response
	 */
	public function getArticleSelectCategoryTree(Request $request): Response
	{
		$list = CategoryEntity::getArticleSelectTree();

		return json(['code' => 0, 'msg' => 'success', 'data' => $list]);
	}


}
