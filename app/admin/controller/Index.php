<?php
/**
 * @Program: TaoLer 2023/3/11
 * @FilePath: app\admin\controller\index.php
 * @Description: Index.php 管理后台首页
 * @LastEditTime: 2023-03-11 10:15:35
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller;

use think\facade\View;
use think\facade\Db;
use think\facade\Session;
use think\Request;
use think\facade\Cache;
use think\facade\Log;
use think\facade\Config;
use app\common\facade\HttpHelper;
use app\common\PayContainer;
use app\admin\model\Cunsult;
use app\model\Comment;
use think\response\Json;
use Yansongda\Pay\Pay;
use think\Container;

class Index extends AdminBaseController
{

	protected $sys_version;
    protected $pn;
    protected $sys;
    protected $domain;
    protected $api;

	public function initialize()
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
		
		return View::fetch();
    }

	public function console1()
	{
		// 评论、帖子状态
        $comm = Db::name('comment')->where(['delete_time'=>0,'status'=>0])->count();
        $forum = Db::name('article')->where(['delete_time'=>0,'status'=>0])->count();
        $user = Db::name('user')->where(['delete_time'=>0,'status'=>0])->count();

        View::assign([
            'pendComms'     => $comm,
            'pendForums'    => $forum,
            'pendUser'      => $user
        ]);

        return View::fetch('console1');
    }
	
	public function console2()
	{
        // 评论、帖子状态
        $comm = Db::name('comment')->where(['delete_time'=>0,'status'=>0])->count();
        $forum = Db::name('article')->field('id')->where(['delete_time'=>0,'status'=>0])->count();
        $user = Db::name('user')->where(['delete_time'=>0,'status'=>0])->count();
		// 回复评论
		$comments = Comment::field('id,article_id,content,create_time,delete_time')->order('id desc')->limit(10)->select();
		$commData = [];
		foreach($comments as $v) {
			if(!is_null($v->article)) {
				$commData[] = [
					'id'			=> $v->id,
					'content'		=> strip_tags($v['content']),
					'create_time'	=> $v['create_time'],
					'url'			=> (string) url('article_detail', ['id' => $v['article_id'], 'ename' => $v->article->cate->ename])
				];
			}
		}

        View::assign([
            'pendComms'     => $comm,
            'pendForums'    => $forum,
            'pendUser'      => $user,
			'comments'		=> $commData,
        ]);
		
        return View::fetch('console2');
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

    /**
     * 系统升级检测
     * @return mixed|string
     */
    public function sysUpgradeCheck()
    {
        $response = HttpHelper::withHost()->get('/v1/upload/check', ['pn'=>$this->pn,'ver'=>$this->sys_version])->toJson();

        if($response->code !== -1){
            return $response->code ? "<span style='color:#b2aeae'>有{$response->up_num}个版本需更新,当前可更新至{$response->version}</span>" : $response->msg;
        }

        return lang('No new messages');
    }
	
	//动态信息
	public function news()
	{
		$data = Request::post(['page/d', 'limit/d']);
		
		$news = Cache::get('news'.$data['page'].'_'.$data['limit']);
		if(empty($news)){
			$news = HttpHelper::withHost()->get('/v1/news', $data)->toJson();
			if($news->code == 0){
				Cache::set('news'.$data['page'].'_'.$data['limit'],$news, 600);
			}
		}

		return $news;
	}
	
	//提交反馈
	public function cunsult()
	{
		if(Request::isPost()){
			$data = Request::post(['type','title','content','post','uid']);

			$response = HttpHelper::withHost()->post('/v1/reply', $data);
			$data['poster'] = Session::get('admin_id');
			unset($data['post']);

			if($response->ok()){
				$result = $response->toJson();
				$res = Cunsult::create($data);
				return json(['code'=>0,'msg' => $result->msg]);
			}

			return json(['code'=>-1,'msg'=>'失败，请稍后再试提交...']);
		}
		
	}
	
	//问题和反馈
	public function reply()
	{
		if(Request::isPost()) {
		
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
		if(Request::isPost()){
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