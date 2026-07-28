<?php
namespace app\index\controller;

use think\facade\View;
use think\facade\Db;
use app\facade\Category as CategoryEntity;

class Category extends IndexBaseController
{
    public function list()
    {
        global $page;
		//动态参数
		$ename = $this->request->param('ename', '');
		$flag = $this->request->param('flag', 'all');
		$page = $this->request->param('page/d', 1);

		// 分类信息
		$cateInfo = CategoryEntity::getCateInfoByEname($ename);

		// 单页分类
		// type 1列表2单页3链接
		if(!is_null($cateInfo) && $cateInfo->type == 2) {
			$singleArticle = Db::name('page')->where('cate_id', $cateInfo->id)->find();

			View::assign('article', $singleArticle);

			return View::fetch('category/' . $cateInfo->tpl . '/single');
		}

		// 当前页url
		$url = (string) url('cate_page', ['ename' => $ename, 'flag' => $flag, 'page' => $page]);
		// 返回最后/前面的字符串
		$path = substr($url, 0, strrpos($url, "/"));
		// 下一页url
		$next = $path . '/' . ++$page . '.html';

		$assignArr = [
			'cateinfo'	=> $cateInfo,
			'cate'	=> $cateInfo,
			'path'	=> $path,
			'page'	=> ++$page,
			'next'  => $next
		];

		View::assign($assignArr);

		$cateView = is_null($cateInfo) ? 'category/list' : 'category/' . $cateInfo->tpl . '/list';

        return View::fetch($cateView);
    }
}
