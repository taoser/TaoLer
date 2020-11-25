<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use app\common\model\Article;
use app\common\model\User;
use app\common\lib\Msgres;

class Index extends BaseController
{	
    public function index()
    {
		$types = input('type');
		//幻灯
        $slider = new \app\common\model\Slider();
        $sliders = $slider->getSliderList();

        $article = new Article();
		//置顶文章
		$artTop = $article->getArtTop(5);
        //首页文章列表,显示20个
        $artList = $article->getArtList(20);
        //热议文章
        $artHot = $article->getArtHot(10);

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
		$res = Cache::get('reply');
		if(!$res){		
			$user = User::withCount('comments')->order(['comments_count'=>'desc','last_login_time'=>'desc'])->limit(20)->select();
			if($user)
			{	
				$res['status'] = 0;
				$res['data'] = array();
					foreach ($user as $key=>$v) {
						
						$u['uid'] = (string) url('user/home',['id'=>$v['id']]);
						$u['count(*)'] = $v['comments_count'];
						if($v['nickname'])
						{
							$u['user'] = ['username'=>$v['nickname'],'avatar'=>$v['user_img']];
						} else {
							$u['user'] = ['username'=>$v['name'],'avatar'=>$v['user_img']];
						}
						$res['data'][] = $u;					
					}					
			}
			Cache::set('reply',$res,3600);
		}
	return json($res);		
	}

	//搜索功能
	public function search()
	{
        //全局查询条件
        $map = []; //所有的查询条件封装到数组中
        //条件1：
        $map[] = ['status','=',1]; //这里等号不能省略

        //实现搜索功能
        $keywords = Request::only(['keywords']);
		//var_dump($keywords['keywords']);
        if(!empty($keywords['keywords'])){
			//条件2
            $map[] = ['title','like','%'.$keywords['keywords'].'%'];
			$artList = Article::where($map)->withCount('comments')->order('create_time','desc')->paginate(10);
			$counts = $artList->count(); 
			$searchs = [
				'artList' => $artList,
				'keywords' => $keywords['keywords'],
				'counts' => $counts
			];	
					
        } else {
			return response('输入非法');
		}
		View::assign($searchs);
		//友情链接
		$friend_links = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>6])->whereTime('slid_over','>=',time())->field('slid_name,slid_href')->select();
		
		//	查询热议
		$artHot = Article::withCount('comments')->field('title,comments_count')->where('status',1)->whereTime('create_time', 'year')->order('comments_count','desc')->limit(10)->select();
		
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
				return json(['code'=>0,'msg'=>'']);
				//return Msg::success('')
			}
		}else {
			return Msgres::error('illegal_request');
		}
	}

}
