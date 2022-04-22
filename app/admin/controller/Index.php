<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Session;
use think\facade\Request;
use think\facade\Cache;
use think\facade\Lang;
use app\admin\model\Admin;
use app\admin\model\Article;
use app\admin\model\Cunsult;
use think\facade\Config;
use taoler\com\Api;

class Index extends AdminController
{
/*	
	protected function initialize()
    {
        parent::initialize();
    }
*/	
	public function __construct()
	{
        //控制器初始化显示左侧导航菜单
        parent::initialize();

		$this->sys_version = Config::get('taoler.version');
		$this->pn = Config::get('taoler.appname');
		$this->sys = $this->getSystem();
		$this->domain = $this->getHttpUrl($this->sys['domain']);
		$this->api = $this->sys['api_url'];
		if(empty($this->api)){
			$baseUrl = $this->sys['base_url'];
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

    public function home()
	{
		// 评论、帖子状态
		$comm = Db::name('comment')->field('id')->where(['delete_time'=>0,'status'=>0])->select();
		$forum = Db::name('article')->field('id')->where(['delete_time'=>0,'status'=>0])->select();
		$comms = count($comm);
		$forums = count($forum);
		
		// 用户注册数据
		$monthTime = Cache::get('monthTime');
		if(!$monthTime){
			$time = Db::name('user')->where('delete_time',0)->whereMonth('create_time')->order('create_time','asc')->column('create_time');
			$monthTime = [];//当月有注册的日期
			foreach($time as $v){//
				$data = date('m-d',$v);
				if(!in_array($data,$monthTime)){
					$monthTime[] = $data;
				};
			}
			$userDayCount = [];//每天注册用户数
			foreach($monthTime as $d){
				$userArr = Db::name('user')->whereDay('create_time',date("Y").'-'.$d)->select();
				$userDayCount[] = count($userArr);
			}
		
			$monthTime = implode(',',$monthTime);	//数组转字符串
			$monthUserCount = implode(',',$userDayCount);
			Cache::set('monthTime',$monthTime,3600);
			Cache::set('monthUserCount',$monthUserCount,3600);
		}
		

		View::assign(['comms'=>$comms,'forums'=>$forums,'monthTime'=>$monthTime,'monthUserCount'=>Cache::get('monthUserCount')]);
        return View::fetch();
    }
	
	//版本检测
	public function getVersion(){
		
		$verCheck = Api::urlPost($this->sys['upcheck_url'],['pn'=>$this->pn,'ver'=>$this->sys_version]);
		if($verCheck->code !== -1){
			$versions = $verCheck->code ? "<span style='color:red'>有{$verCheck->up_num}个版本需更新,当前可更新至{$verCheck->version}</span>" : $verCheck->msg;
			return $versions;
		} else {
			return lang('No new messages');
		}
	}
	
	//本周发帖
	public function forums()
	{
		$forumList = Db::name('article')
			->alias('a')
			->join('user u','a.user_id = u.id')
			->join('cate c','a.cate_id = c.id')
			->field('a.id as aid,title,name,catename,pv')
			->where('a.delete_time',0)
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
				    //$url = (string) str_replace("admin","index",$this->domain.url('article/detail',['id'=>$v['aid']]));
					$url = $this->getRouteUrl($v['aid']);
				$res['data'][]= ['id'=>$url,'title'=>htmlspecialchars($v['title']),'name'=>$v['name'],'catename'=>$v['catename'],'pv'=>$v['pv']];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'本周还没有发帖！'];
			}
			return json($res);
	}
	
	//本周评论
	public function replys()
	{
		if(Request::isAjax()){
		
			$replys = Db::name('comment')
				->alias('a')
				->join('user u','a.user_id = u.id')
				->join('article c','a.article_id = c.id')
				->field('a.content as content,title,c.id as cid,name')
				->where('c.delete_time',0)
				->whereWeek('a.create_time')
				->order('a.create_time', 'desc')
				->paginate(10);
			
			$count = $replys->total();
			$res = [];
			if ($count) {
				$res = ['code'=>0,'msg'=>'','count'=>$count];
				foreach($replys as $k => $v){
					$res['data'][] = ['content'=>htmlspecialchars($v['content']),'title'=>htmlspecialchars($v['title']),'cid'=>$this->getRouteUrl($v['cid']),'name'=>$v['name']];
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
		$data = Request::only(['page', 'limit']);
		$url = $this->api.'/v1/news?'.'page='.$data['page'].'&'.'limit='.$data['limit'];
		$news = Cache::get('news'.$data['page'].'_'.$data['limit']);
		if(empty($news)){
			$news = Api::urlGet($url);
			if($news->code == 0){
				Cache::set('news'.$data['page'].'_'.$data['limit'],$news,600);
			}
		}
		return $news;
	}
	
	//提交反馈
	public function cunsult()
	{
		$url = $this->api.'/v1/reply';
		//$mail = Db::name('system')->where('id',1)->value('auth_mail');	//	bug邮件发送
		if(Request::isAjax()){
			$data = Request::only(['type','title','content','post']);
			$apiRes = Api::urlPost($url,$data);
			$data['poster'] = Session::get('admin_id');
			unset($data['post']);
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