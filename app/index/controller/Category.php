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
		$cateInfo = CategoryEntity::getCateInfoByEname($ename);
		if(!$cateInfo && $ename !== 'all') {
			throw new \think\exception\HTTPException('404', '分类不存在');
		}

		// 单页分类
		// type 1列表2单页3链接
		if($ename !== 'all' && $cateInfo->type == 2) {
			$single = CategoryEntity::getSinglePage($cateInfo->id);
			if(!$single) {
				throw new \think\exception\HTTPException('404', '页面不存在');
			}
			View::assign('article', $single);
			return View::fetch('category/' . $cateInfo->tpl . '/single');
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
			'category'	=> $cateInfo,
			'path'	=> $path,
			'page'	=> ++$page,
			'next'  => $next
		];

		View::assign($assignArr);

		$cateView = is_null($cateInfo) ? 'category/list' : 'category/' . $cateInfo->tpl . '/list';

        return View::fetch($cateView);
    }
}
