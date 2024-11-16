<?php

namespace app\controller\index;

use think\facade\View;
use app\model\index\Article;

class Category extends IndexBaseController
{

    private $model = null;

    public function initialize()
    {
        parent::initialize();

        $this->model = new \app\model\index\Category();
    }

    //文章分类
    public function getArticles()
	{
		//动态参数
		$ename = $this->request->param('ename');
		$type = $this->request->param('type', '');
		$page = $this->request->param('page', 1);

		// 分类信息
		$cateInfo = $this->model::getCateInfo($ename);

		if(is_null($cateInfo) && $ename != 'all') {
			// 抛出 HTTP 异常
			throw new \think\exception\HttpException(404, '没有可访问的数据！');
		}
		
        //分类列表
		$articles = $this->model::getArticlesByCategoryEname($ename, $page, $type);
// dump($articles);
		//	热议文章
		$artHot = Article::getHots(10);

		//分页url
		if(empty($type)) {
			$url = (string) url('cate_page',['ename' => $ename,'page' => $page]);
		} else {
			$url = (string) url('cate_type_page',['ename'=>$ename, 'type' => $type, 'page'=>$page]);
		}
		// halt($url);
		$path = substr($url,0,strrpos($url,"/")); //返回最后/前面的字符串

		$assignArr = [
			'ename'		=> $ename,
			'cateinfo'	=> $cateInfo,
			'type'		=> $type,
			// 'artList'	=> $articles,
			// 'articles'	=> $articles,
			// 'artHot'	=> $artHot,
			'path'		=> $path
		];

		View::assign($assignArr);

		$cateView = is_null($cateInfo) ? "index/article/cate" : "index/article/{$cateInfo->detpl}/cate";
		return View::fetch($cateView);
    }
}