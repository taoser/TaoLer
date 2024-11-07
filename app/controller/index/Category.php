<?php

namespace app\controller\index;

use app\common\model\Cate;
use think\facade\View;

class category extends IndexBaseController
{

    private $model = null;

    public function initialize()
    {
        parent::initialize();

        $this->model = new Cate();
    }

    //文章分类
    public function getArticleListByEname()
	{
		$cate = new Cate();
		//动态参数
		$ename = $this->request->param('ename');
		$type = $this->request->param('type','all');
		$page = $this->request->param('page',1);

		// 分类信息
		$cateInfo = $cate->getCateInfo($ename);

		if(is_null($cateInfo)) {
			// 抛出 HTTP 异常
			throw new \think\exception\HttpException(404, '没有可访问的数据！');
		}
		
        //分类列表
		$artList = $this->model->getCateList($ename, $type, $page);

		//	热议文章
		$artHot = $this->model->getArtHot(10);

		//分页url
		$url = (string) url('cate_page',['ename'=>$ename,'type'=>$type,'page'=>$page]);
		$path = substr($url,0,strrpos($url,"/")); //返回最后/前面的字符串


		$assignArr = [
			'ename'		=> $ename,
			'cateinfo'	=> $cateInfo,
			'type'		=> $type,
			'artList'	=> $artList,
			'artHot'	=> $artHot,
			'path'		=> $path
		];

		View::assign($assignArr);

		$cateView = is_null($cateInfo) ? "index/article/cate" : "index/article/{$cateInfo->detpl}/cate";
		return View::fetch($cateView);
    }
}