<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\model\Article;
use app\common\model\User;
use app\common\model\Cate;
use app\common\model\Comment;


class Index extends BaseController
{	
    public function index()
    {
		$types = input('type');
		//幻灯
		$sliders = Db::name('slider')->where('slid_status',1)->where('delete_time',0)->where('slid_type',1)->whereTime('slid_over','>=',time())->select();
	
		//置顶文章
		$artTop = Article::field('id,title,cate_id,user_id,create_time,is_top')->where(['is_top'=>1,'status'=>1,'delete_time'=>0])->with([
            'cate' => function($query){
				$query->where('delete_time',0)->field('id,catename');
            },
			'user' => function($query){
				$query->field('id,name,nickname,user_img,area_id');
			}
        ])->withCount(['comments'])->order('create_time','desc')->limit(5)->withCache(30)->select();
	
		//首页文章显示15条
		$artList = Article::field('id,title,cate_id,user_id,create_time,is_hot')->with([
            'cate' => function($query){
				$query->where('delete_time',0)->field('id,catename');
            },
			'user' => function($query){
				$query->field('id,name,nickname,user_img,area_id');
			}
        ])->withCount(['comments'])->where(['status'=>1,'delete_time'=>0])->order('create_time','desc')->limit(15)->withCache(30)->select();

		//热议文章
		$artHot = Article::field('id,title')->withCount('comments')->where(['status'=>1,'delete_time'=>0])->whereTime('create_time', 'year')->order('comments_count','desc')->limit(10)->select();
		
		//首页赞助
		$ad_index = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>3])->whereTime('slid_over','>=',time())->select();
		
		//首页右栏
		$ad_comm = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>2])->whereTime('slid_over','>=',time())->select();
		
		//友情链接
		$friend_links = Db::name('slider')->where(['slid_status'=>1,'delete_time'=>0,'slid_type'=>6])->whereTime('slid_over','>=',time())->field('slid_name,slid_href')->select();
		
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
		$user = User::withCount('comments')->order('comments_count','desc')->limit(20)->select();
		if($user)
		{	
			$res['status'] = 0;
			$res['data'] = array();
				foreach ($user as $key=>$v) {
					
					$u['uid'] = $v['id'];
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
	return json($res);		
	}

	//搜索功能
	public function search()
	{
		//$this->showUser();
        //全局查询条件
        $map = []; //所有的查询条件封装到数组中
        //条件1：
        $map[] = ['status','=',1]; //这里等号不能省略

        //实现搜索功能
        $keywords = input('keywords');
        if(!empty($keywords)){
			//条件2
            $map[] = ['title','like','%'.$keywords.'%'];
			$artList = Article::where($map)->withCount('comments')->order('create_time','desc')->paginate(10);
			$counts = $artList->count(); 
			$searchs = [
				'artList' => $artList,
				'keywords' => $keywords,
				'counts' => $counts
			];
			
		View::assign($searchs);
			//友情链接
		$friend_links = Db::name('friend_link')->field('linkname,linksrc')->select();
		View::assign('flinks',$friend_links);
		
		//	查询热议
		$artHot = Article::withCount('comments')->field('title,comments_count')->where('status',1)->whereTime('create_time', 'year')->order('comments_count','desc')->limit(10)->select();
		View::assign('artHot',$artHot);
			    	
        } else{
			return '请输入关键词';
		}
		return View::fetch('search');
	}

    public function jump()
    {
        $username = Request::param('username');
        $u = Db::name('user')->whereOr('nickname', $username)->whereOr('name', $username)->find();
        return redirect('index/user/home',['id'=>$u['id']]);

    }

}
