<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\App;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use app\facade\Article;
use app\common\lib\Msgres;

class Index extends BaseController
{
    /**
     * 首页
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
		$types = input('type');

		//幻灯
        $slider = new \app\common\model\Slider();
        $sliders = $slider->getSliderList();

		//置顶文章
		$artTop = Article::getArtTop(5);
        //首页文章列表,显示20个
        $artList = Article::getArtList(20);
        //热议文章
        $artHot = Article::getArtHot(10);

		//首页赞助
		$ad_index = Cache::get('adindex');
		if(!$ad_index){
			$ad_index = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>3])->whereTime('slid_over','>=',time())->select();
			Cache::set('adindex',$ad_index,3600);
		}
		
		//首页右栏
		$ad_comm = Cache::get('adcomm');
		if(!$ad_comm){
			$ad_comm = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>2])->whereTime('slid_over','>=',time())->select();
			Cache::set('adcomm',$ad_comm,3600);
		}
		
		//友情链接
		$friend_links = Cache::get('flinks');
		if(!$friend_links){
			$friend_links = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>6])->whereTime('slid_over','>=',time())->field('slid_name,slid_href')->select();
			Cache::set('flinks',$friend_links,3600);
		}
		
		$vs = [
			'slider'	=>	$sliders,
			'artTop'	=>	$artTop,
			'artList'	=>	$artList,
			'artHot'	=>	$artHot,
			'type'		=>	$types,
			'ad_index'	=>	$ad_index,
			'ad_comm'	=>	$ad_comm,
			'flinks'	=>	$friend_links,
		];
		View::assign($vs);

		return View::fetch();
    }
	
	//回帖榜
	public function reply()
	{
        $comment = new \app\common\model\Comment();
        return $comment->reply(20);
	}

	//搜索功能
	public function search()
	{
        $ser = Request::only(['keywords']);

	    $search = new \app\index\controller\Search();
	    $artList = $search->getSearch($ser['keywords']);
        $counts = $artList->count();
        $searchs = [
            'artList' => $artList,
            'keywords' => $ser['keywords'],
            'counts' => $counts
        ];

		//友情链接
		$friend_links = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>6])->whereTime('slid_over','>=',time())->field('slid_name,slid_href')->select();
        //	查询热议
		//$article = new Article()1;
		$artHot = Article::getArtHot(10);

        View::assign($searchs);
		View::assign(['flinks'=>$friend_links,'artHot'=>$artHot]);
		return View::fetch('search');
	}

    public function jump()
    {
        $username = Request::param('username');
        $u = Db::name('user')->whereOr('nickname', $username)->whereOr('name', $username)->find();
        return redirect((string) url('user/home',['id'=>$u['id']]));

    }
	
	public function language()
	{
		if(request()->isPost()){
			$language = new \app\common\controller\Language;
			$lang = $language->select(input('language'));
			if($lang){
				return Msgres::success();
			}
		}else {
			return Msgres::error('illegal_request');
		}
	}

}
