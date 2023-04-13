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
use think\App;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\model\Comment as CommentModel;



class Comment extends AdminController
{

    protected $model;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new \app\common\model\Comment();
    }



    /**
     * 浏览
     * @return string
     */
	public function index()
    {
        return View::fetch();
    }

    public function list1()
    {
        $data = Request::only(['name','content','status']);
        $map = $this->getParamFilter($data);
        $where = [];
        if(!empty($map['content'])){
            $where[] = ['content', 'like', $map['content'].'%'];
        }
        if(isset($data['status'])){
            $where[] = ['status', '=', (int) $data['status']];
        }

        if(isset($data['name'])){
            $userId = Db::name('user')->where('name',$data['name'])->value('id');
            $where[] = ['user_id', '=', $userId];
        }
        unset($map);

        $list = $this->model->getCommentList($where, input('page'), input('limit'));
        $res = [];
        if($list['total']) {
            $res = ['code' =>0, 'msg' => 'ok', 'count' => $list['total']];
            foreach($list['data'] as $k => $v){
                $res['data'][] = [
                    'id'        => $v['id'],
                    'replyer'   => $v['user']['name'],
                    'title'     => $v['article']['title'],
                    'avatar'    => $v['user']['user_img'],
                    'content'   => strip_tags($v['content']),
                    'replytime' => $v['create_time'],
                    'check'     => $v['status'],
                    //'url'       => $this->getArticleUrl($v['article_id'], 'index', $v->article->cate->ename),
                ];
            }
            return json($res);
        }
        return json(['code' => 0, 'msg' => 'no data']);
    }

	//帖子评论
	public function list()
	{
        $data = Request::only(['name','content','status']);
        $map = array_filter($data);
        $where = array();
        if(!empty($map['content'])){
            $where[] = ['a.content','like', $map['content'].'%'];
            unset($map['content']);
        }
        if(isset($data['status']) && $data['status'] !== '' ){
            $where[] = ['a.status','=',(int)$data['status']];
            unset($map['status']);
        }
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
            ->paginate([
                'list_rows' => input('limit'),
                'page' => input('page')
            ]);
        $count = $replys->total();
        if ($count) {
            $res = ['code'=>0,'msg'=>'','count'=>$count];
            foreach($replys as $k => $v){
                $res['data'][] = [
                    'id'        => $v['aid'],
                    'replyer'   => $v['name'],
                    'title'     => htmlspecialchars($v['title']),
                    'avatar'    => $v['user_img'],
                    'content'   => strip_tags($v['content']),
                    'replytime' => date("Y-m-d",$v['create_time']),
                    'check'     => $v['astatus'],
                    'url'       => $this->getArticleUrl($v['cid'],'index',$v['ename'])
                ];
            }
        } else {
            $res = ['code'=>-1,'msg'=>'没有查询结果！'];
        }
        return json($res);

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
			if($data['status'] == 1) return json(['code'=>0,'msg'=>'评论审核通过','icon'=>6]);
            return json(['code'=>0,'msg'=>'评论被禁止','icon'=>5]);
		}
        return json(['code'=>-1,'msg'=>'审核出错']);

	}
	

	
	//array_filter过滤函数
	public function  filtr($arr)
    {
        if($arr === '' || $arr === null) return false;
        return true;
	}

}
