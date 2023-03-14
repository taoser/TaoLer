<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\content\Comment.php
 * @Description: Comment
 * @LastEditTime: 2023-03-14 15:38:55
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\content;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\model\Comment as CommentModel;



class Comment extends AdminController
{
    /**
     * 浏览
     * @return string
     */
	public function index()
    {
        return View::fetch();
    }
	
	//帖子评论
	public function list()
	{
		if(Request::isAjax()) {
			$data = Request::only(['name','content','status']);
			$map = array_filter($data);
			$where = array();
			if(!empty($map['content'])){
				$where[] = ['a.content','like','%'.$map['content'].'%'];
				unset($map['content']);
			}
			if(isset($data['status']) && $data['status'] !== '' ){
				$where[] = ['a.status','=',(int)$data['status']];
				unset($map['status']);
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
				->join('cate ca','c.cate_id = ca.id')
				->field('a.id as aid,name,ename,appname,title,user_img,a.content as content,a.create_time as create_time,a.status as astatus,c.id as cid')
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
					$url = $this->getRouteUrl($v['cid'],$v['ename'], $v['appname']);
					//$res['data'][] = ['id'=>$v['id'],'replyer'=>$v->user->name,'cardid'=>$v->article->title,'avatar'=>$v->user->user_img,'content'=>$v['content'],'replytime'=>$v['create_time']];
					$res['data'][] = [
                        'id'=>$v['aid'],
                        'replyer'=>$v['name'],
                        'title'=>htmlspecialchars($v['title']),
                        'avatar'=>$v['user_img'],
                        'content'=>htmlspecialchars($v['content']),
                        'replytime'=>date("Y-m-d",$v['create_time']),
                        'check'=>$v['astatus'],
                        'url'=>$url
                    ];
				}
			} else {
				$res = ['code'=>-1,'msg'=>'没有查询结果！'];
			}
			return json($res);
		}

	}
	
	//评论编辑
	public function edit()
	{
		return View::fetch();
	}
	
	//评论删除
	public function delete($id)
	{
		if(Request::isAjax()){
            $arr = explode(",",$id);
            foreach($arr as $v){
                $comm = CommentModel::find($v);
                $result = $comm->delete();
            }
            if($result){
                return json(['code'=>0,'msg'=>'删除成功']);
            }else{
                return json(['code'=>-1,'msg'=>'删除失败']);
            }
        }

	}
	//评论审核
	public function check()
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
	

	
	//array_filter过滤函数
	public function  filtr($arr){
			if($arr === '' || $arr === null){
				return false;
			}
        return true;
	}

}
