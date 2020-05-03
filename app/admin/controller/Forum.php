<?php

namespace app\admin\controller;

use app\common\controller\AdminController;
use app\admin\validate\Admin;
use app\admin\model\Admin as adminModel;
use app\common\model\Cate;
use app\common\model\Comment;
use app\common\model\Article;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Session;
use think\exception\ValidateException;

class Forum extends AdminController
{
    //帖子列表
	public function list()
	{
		if(Request::isAjax()){	
		$data = Request::only(['id','name','title','sec']);
		$where =array();
		if (!empty($data['sec'])) {
			switch ($data['sec']) {
			case '1':
			$data['a.status'] = 1;
			break;
			case '2':
			$data['is_top'] = 1;
			break;
			case '3':
			$data['is_hot'] = 1;
			break;
			case '4':
			$data['is_reply'] = 0;
			break;
			case '5':
			$data['a.status'] = 0;
			break;
			}
		}
		unset($data['sec']);
		unset($data['status']);

		if(!empty($data['id'])){
			$data['a.id'] = $data['id'];
			unset($data['id']);
		}
		
		if(!empty($data['title'])){
			$where[] = ['title', 'like', '%'.$data['title'].'%'];
			unset($data['title']);
		}

		$map = array_filter($data,[$this,"filtr"]);

			$forumList = Db::name('article')
			->alias('a')
			->join('user u','a.user_id = u.id')
			->field('a.id as aid,name,user_img,title,a.update_time as update_time,is_top,is_hot,is_reply,a.status as status')
			->where('a.delete_time',0)
			->where($map)
			->where($where)
			->order('a.create_time', 'desc')
			->paginate(15);
			$res = [];
			$count = $forumList->total();
			if($count){
				$res['code'] = 0;
				$res['msg'] = '';
				$res['count'] = $count;
				foreach($forumList as $k=>$v){
				$res['data'][]= ['id'=>$v['aid'],'poster'=>$v['name'],'avatar'=>$v['user_img'],'content'=>$v['title'],'posttime'=>date("Y-m-d",$v['update_time']),'top'=>$v['is_top'],'hot'=>$v['is_hot'],'reply'=>$v['is_reply'],'check'=>$v['status']];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'没有查询结果！'];
			}
			return json($res);
		}
		return View::fetch();
	}
	
	//编辑帖子
	public function listform()
	{
		if(Request::isAjax()){
			$data = Request::param();
			$form = Db::name('article')->find($data['id']);
			//halt($form);
		}
		return View::fetch();
	}
	
	//删除帖子
	public function listdel($id)
	{
		if(Request::isAjax()){
			$article =Article::find($id);
			$result = $article->together(['comments'])->delete();
			
				if($result){
					return json(['code'=>0,'msg'=>'删除成功']);
				}else{
					return json(['code'=>-1,'msg'=>'删除失败']);
				}
			}
	}
	//置顶帖子
	public function top()
	{
		//
	}
	//加精帖子
	public function hot()
	{
		//
	}
	//审核帖子
	public function check()
	{
		$data = Request::param();

		//获取状态
		$res = Db::name('article')->where('id',$data['id'])->save(['status' => $data['status']]);
		if($res){
			if($data['status'] == 1){
				return json(['code'=>0,'msg'=>'帖子审核通过','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'帖子被禁止','icon'=>5]);
			}
			
		}else {
			return json(['code'=>-1,'msg'=>'审核出错']);
		}
	}
	
	//帖子分类
	public function tags()
	{
		if(Request::isAjax()){
			$list = Cate::select();
			if($list){
				$res['code'] = 0;
				$res['msg'] = '';
				$res['count']= count($list);
				$res['data'] = [];
				foreach($list as $k=>$v){
				$res['data'][] = ['sort'=>$v['sort'],'id' => $v['id'],'tags'=>$v['catename'],'ename'=>$v['ename'],'is_hot'=>$v['is_hot'],'desc'=>$v['desc']];
				}
			}
			return json($res);
		}
		return View::fetch();
	}
	
	//添加帖子分类
	public function addtags()
	{
		if(Request::isAjax()){
		$data = Request::param();
		$list = Db::name('cate')->cache('catename')->save($data);
		
			if($list){
				return json(['code'=>0,'msg'=>'添加分类成功']);
			}else{
				return json(['code'=>-1,'msg'=>'添加分类失败']);
			}
		}
		return view('tagsform');
		
	}
	
	//编辑帖子分类
	public function tagsform()
	{
		if(Request::isAjax()){
		$data = Request::param();
		$list = Db::name('cate')->cache('catename')->update($data);
		
			if($list){
				return json(['code'=>0,'msg'=>'修改分类成功']);
			}else{
				return json(['code'=>-1,'msg'=>'修改分类失败']);
			}
		}
		return View::fetch();
	}
	
	//删除帖子分类
	public function tagsdelete()
	{
		if(Request::isAjax()){
		$data = Request::param();

		$cate = new Cate;
		$result = $cate->del($data);
		
		
			if($result == 1){
				Cache::tag('catename')->clear();
				return json(['code'=>0,'msg'=>'删除分类成功']);
			}else{
				return json(['code'=>-1,'msg'=>'删除分类失败']);
			}
		}
	}
	
	//帖子评论
	public function replys()
	{
		if(Request::isAjax()) {
			$data = Request::only(['name','content']);
			$map = array_filter($data);
			$where = array();
			if(!empty($map['content'])){
				$where[] = ['a.content','like','%'.$map['content'].'%'];
				unset($map['content']);
			}

/*			
			$replys = Comment::field('id,article_id,user_id,content,create_time')->with([
				'user' => function($query){
				$query->field('id,name,user_img');
			},
			'article' => function($query){
				$query->field('id,title');
			}
			])->paginate(15);
*/			
			$replys = Db::name('comment')
				->alias('a')
				->join('user u','a.user_id = u.id')
				->join('article c','a.article_id = c.id')
				->field('a.id as aid,name,title,user_img,a.content as content,a.create_time as create_time,a.status as astatus,c.id as cid')
				->where('a.delete_time',0)
				->where($map)
				->where($where)
				->order('a.create_time', 'desc')
				->paginate(15);
			
			$count = $replys->total();
			$res = [];
			if ($count) {
				$res = ['code'=>0,'msg'=>'','count'=>$count];
				foreach($replys as $k => $v){
					//$res['data'][] = ['id'=>$v['id'],'replyer'=>$v->user->name,'cardid'=>$v->article->title,'avatar'=>$v->user->user_img,'content'=>$v['content'],'replytime'=>$v['create_time']];
					$res['data'][] = ['id'=>$v['aid'],'replyer'=>$v['name'],'cardid'=>$v['title'],'avatar'=>$v['user_img'],'content'=>$v['content'],'replytime'=>date("Y-m-d",$v['create_time']),'check'=>$v['astatus'],'cid'=>$v['cid']];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'没有查询结果！'];
			}
			return json($res);
			}
		
		return View::fetch();
	}
	
	//评论编辑
	public function replysform()
	{
		return View::fetch();
	}
	//评论删除
	public function redel($id)
	{
		if(Request::isAjax()){
			$comm =Comment::find($id);
			$result = $comm->delete();
			
				if($result){
					return json(['code'=>0,'msg'=>'删除成功']);
				}else{
					return json(['code'=>-1,'msg'=>'删除失败']);
				}
			}
	}
	//评论审核
	public function recheck()
	{
		$data = Request::param();

		//获取状态
		$res = Db::name('comment')->where('id',$data['id'])->save(['status' => $data['status']]);
		if($res){
			if($data['status'] == 1){
				return json(['code'=>0,'msg'=>'评论审核通过','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'评论被禁止','icon'=>5]);
			}
			
		}else {
			return json(['code'=>-1,'msg'=>'审核出错']);
		}
	}
	
	//帖子分类开启热点
	//评论审核
	public function tagshot()
	{
		$data = Request::only(['id','is_hot']);
		$cate = Db::name('cate')->save($data);
		if($cate){
			if($data['is_hot'] == 1){
				return json(['code'=>0,'msg'=>'设置热点成功','icon'=>6]);
			} else {
				return json(['code'=>0,'msg'=>'取消热点显示','icon'=>5]);
			}
		}else{
			$res = ['code'=>-1,'msg'=>'热点设置失败'];
		} 
		return json($res);
	}
	
	//array_filter过滤函数
	public function  filtr($arr){
			if($arr === '' || $arr === null){
				return false;
			}
        return true;
	}
}
