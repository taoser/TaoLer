<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
use app\admin\model\Admin;
use app\admin\model\Article;
use think\facade\Config;

class Index extends AdminController
{
	
	protected function initialize()
    {
        parent::initialize();
		$this->domain = Request::scheme().'://www.'.Request::rootDomain();
		//dump($this->domain);
    }

    public function index()
	{
        return View::fetch('index');
    }
	

    public function set()
	{
        return view();
    }

    public function message()
	{
        return view();
    }

    public function home(){
		$sys = Db::name('system')->find(1);
		$now = time();
		$count = $now-$sys['create_time'];
		$days = floor($count/86400);
		$hos = floor(($count%86400)/3600);
		$mins = floor(($count%3600)/60);
		View::assign(['sys'=>$sys,'day'=>$days,'hos'=>$hos,'mins'=>$mins]);
        return View::fetch();
    }
	
	//本周发帖
	public function forums()
	{
		$forumList = Db::name('article')
			->alias('a')
			->join('user u','a.user_id = u.id')
			->join('cate c','a.cate_id = c.id')
			->field('a.id as aid,title,name,catename,pv')
			->whereWeek('a.create_time')
			->order('a.create_time', 'desc')
			->paginate(10);
			$res = [];
			$count = $forumList->total();
			if($count){
				$res['code'] = 0;
				$res['msg'] = '';
				$res['count'] = $count;
				foreach($forumList as $k=>$v){
				$res['data'][]= ['id'=>str_replace("admin","index",$this->domain.(string) url('article/detail',['id'=>$v['aid']])),'title'=>$v['title'],'name'=>$v['name'],'catename'=>$v['catename'],'pv'=>$v['pv']];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'本周还没有发帖！'];
			}
			return json($res);
	}
	
	//本周评论
	public function replys()
	{
		if(Request::isAjax()) {
		
			$replys = Db::name('comment')
				->alias('a')
				->join('user u','a.user_id = u.id')
				->join('article c','a.article_id = c.id')
				->field('a.content as content,title,c.id as cid,name')
				->whereWeek('a.create_time')
				->order('a.create_time', 'desc')
				->paginate(10);
			
			$count = $replys->total();
			$res = [];
			if ($count) {
				$res = ['code'=>0,'msg'=>'','count'=>$count];
				foreach($replys as $k => $v){
					$res['data'][] = ['content'=>$v['content'],'title'=>$v['title'],'cid'=>str_replace("admin","index",$this->domain.(string) url('article/detail',['id'=>$v['cid']])),'name'=>$v['name']];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'本周还没评论'];
			}
			return json($res);
			}
	}
	
	
	
	  public function layout(){
		
        return view();
    }
}