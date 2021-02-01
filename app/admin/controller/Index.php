<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
use think\facade\Cache;
use app\admin\model\Admin;
use app\admin\model\Article;
use app\admin\model\Cunsult;
use think\facade\Config;
use taoler\com\Api;

class Index extends AdminController
{
	
	protected function initialize()
    {
        parent::initialize();
		$this->domain = Request::scheme().'://www.'.Request::rootDomain();
		$this->api = Db::name('system')->where('id',1)->value('api_url');
		if(empty($this->api)){
			$baseUrl = Db::name('system')->where('id',1)->value('base_url');
			$this->api = strstr($baseUrl,'/v',true);
		}
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
	
	//动态信息
	public function news()
	{
		$page = Request::param('page');
		$url = $this->api.'/v1/news?'.Request::query();
		$news = Cache::get('news'.$page);
		if(is_null($news)){
			$news = Api::urlGet($url);
			Cache::set('news'.$page,$news,600);
		}
		
		if($news){
			return $news;
		}else{
			return json(['code'=>-1,'msg'=>'稍后获取内容...']);
		}
		
	}
	
	//提交反馈
	public function cunsult()
	{
		$url = $this->api.'/v1/reply';
		
		//$mail = Db::name('system')->where('id',1)->value('auth_mail');	//	bug邮件发送
		if(Request::isAjax()){
			$data = Request::only(['type','title','content']);
			$data['poster'] = 3;	//公共id

			$apiRes = Api::urlPost($url,$data);

			if($apiRes){
				$res = Cunsult::create($data);
				if($res->id){
					//$result = mailto($mail,$data['title'],'我的问题类型是'.$data['type'].$data['content']);
					$res = ['code'=>0,'msg'=>$apiRes->msg];
				} else {
					$res = ['code'=>0,'msg'=>$apiRes->msg];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'失败，请稍后再试提交...'];
			}
			return json($res);
		}
		
	}
	
	//问题和反馈
	public function reply()
	{
		if(Request::isAjax()) {
		
			$replys = Db::name('cunsult')
				->whereWeek('create_time')
				->order('create_time', 'desc')
				->paginate(10);
			
			$count = $replys->total();
			$res = [];
			if ($count) {
				$res = ['code'=>0,'msg'=>'','count'=>$count];
				foreach($replys as $k => $v){
					$res['data'][] = ['id'=>$v['id'],'content'=>$v['content'],'title'=>$v['title'],'time'=>Date('Y-m-d',$v['create_time'])];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'本周还没问题'];
			}
			return json($res);
			}
	}
	
	//删除反馈
	public function delReply()
	{
		if(Request::isAjax()){
			$res = Db::name('cunsult')->delete(input('id'));
			 
			if($res){
				$res = ['code'=>0,'msg'=>'删除成功！'];
			}else{
				$res = ['code'=>-1,'msg'=>'删除失败！'];
			}
			return json($res);
		}
	}
	
	
	
	  public function layout(){
		
        return view();
    }
}