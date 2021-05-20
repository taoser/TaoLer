<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use app\common\model\Article;
use app\common\model\User;
use app\common\model\Cate;
use app\common\model\Comment;


class Doc extends BaseController
{	

    public function timeline()
    {
		$types = input('type');
		//幻灯
		$sliders = Cache::get('slider');
		if(!$sliders){
			$sliders = Db::name('slider')->where('slid_status',1)->where('delete_time',0)->where('slid_type',1)->whereTime('slid_over','>=',time())->select();
			Cache::set('slider',$sliders,3600);
		}
		
		//更新日志
		$timeline = Db::name('time_line')->where('delete_time',0)->order('create_time','desc')->select();
		
		//热议文章
		$artHot = Article::field('id,title')->withCount('comments')->where(['status'=>1,'delete_time'=>0])->whereTime('create_time', 'year')->order('comments_count','desc')->limit(10)->select();

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
			'timeline'	=>	$timeline,
			'artHot'	=>	$artHot,
			'type'		=>	$types,
			'ad_index'	=>	$ad_index,
			'ad_comm'	=>	$ad_comm,
			'flinks'	=>	$friend_links,
			'jspage'	=>	'',
		];
		View::assign($vs);

		return View::fetch();
    }

}
