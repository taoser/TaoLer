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
			$forumList = Db::name('article')
			->alias('a')
			->join('user u','a.user_id = u.id')
			->field('a.id as aid,name,user_img,title,a.update_time as update_time,is_top,is_hot,a.status as astatus')
			->where('a.delete_time',0)
			->order('a.create_time', 'desc')
			->paginate(15);
			$res = [];
			if($forumList){
				$res['code'] = 0;
				$res['msg'] = '';
				$res['count'] = $forumList->total();
				foreach($forumList as $k=>$v){
				$res['data'][]= ['id'=>$v['aid'],'poster'=>$v['name'],'avatar'=>$v['user_img'],'content'=>$v['title'],'posttime'=>date("Y-m-d",$v['update_time']),'top'=>$v['is_top'],'hot'=>$v['is_hot'],'check'=>$v['astatus']];
				}
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
				$res['data'][] = ['id' => $v['id'],'tags'=>$v['catename'],'sort'=>$v['sort'],'ename'=>$v['ename']];
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
		//halt($data);
		$list = Db::name('cate')->save($data);
		
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
		//halt($data);
		$list = Db::name('cate')->where('id',$data['id'])->save($data);
		
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
				->order('a.create_time', 'desc')
				->paginate(15);
			
			$count = $replys->total();
			$res = [];
			if ($replys) {
				$res = ['code'=>0,'msg'=>'','count'=>$count];
				foreach($replys as $k => $v){
					//$res['data'][] = ['id'=>$v['id'],'replyer'=>$v->user->name,'cardid'=>$v->article->title,'avatar'=>$v->user->user_img,'content'=>$v['content'],'replytime'=>$v['create_time']];
					$res['data'][] = ['id'=>$v['aid'],'replyer'=>$v['name'],'cardid'=>$v['title'],'avatar'=>$v['user_img'],'content'=>$v['content'],'replytime'=>date("Y-m-d",$v['create_time']),'check'=>$v['astatus'],'cid'=>$v['cid']];
				}
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
}
