<?php
namespace app\index\controller;

use think\Request;
use think\Response;
use think\facade\View;
use think\facade\Db;
use app\facade\Category as CategoryEntity;

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
		if(!$categoryInfo && $ename !== 'all') {
			throw new \think\exception\HTTPException('404', '分类不存在');
		}

		// 单页分类
		// type 1列表2单页3链接
		if($ename !== 'all' && $categoryInfo->type == 2) {
			$single = CategoryEntity::getSinglePage($categoryInfo->id);
			if(!$single) {
				throw new \think\exception\HTTPException('404', '页面不存在');
			}
			View::assign('article', $single);
			return View::fetch('category/' . $categoryInfo->tpl . '/single');
		}

		if(empty($flag)) {
			$url = (string) url('cate_page', ['ename' => $ename, 'page' => $page]);
		} else {
			$url = (string) url('cate_flag_page', ['ename' => $ename, 'flag' => $flag, 'page' => $page]);
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
}
