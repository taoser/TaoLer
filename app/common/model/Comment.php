<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-08-16 12:18:29
 * @LastEditors: TaoLer
 * @Description: 评论模型
 * @FilePath: \TaoLer\app\common\model\Comment.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Comment extends Model
{	
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
	public function article()
	{
		//评论关联文章
		return $this->belongsTo('Article','article_id','id');
	}
	
	public function user()
	{
		//评论关联用户
		return $this->belongsTo('User','user_id','id');
	}

	//获取评论
    public function getComment($id, $page)
    {
        return $this::with(['user'])
            ->where(['article_id'=>(int)$id,'status'=>1])
            ->order(['cai'=>'asc','create_time'=>'asc'])
            ->paginate(['list_rows'=>10, 'page'=>$page])
            ->toArray();

    }

    //回帖榜
    public function reply($num)
    {
        try {
            $user = User::field('id,user_img,name,nickname')
            ->withCount(['comments'=> function($query) {
                $query->where(['status'=>1]);
            }])
            ->order(['comments_count'=>'desc','last_login_time'=>'desc'])
            ->limit($num)
            ->withCache(300)
            ->select()
            ->toArray();
        } catch(\Exception $e) {
            return json(['status'=>-1,'msg'=>$e->getMessage()]);
        }

        if(count($user)) {
            $res['status'] = 0;
            $res['data'] = array();
            foreach ($user as $key=>$v) {
                $u['uid'] = (string) url('user_home',['id'=>$v['id']]);
                // $u['uid'] = (string) \think\facade\Route::buildUrl('user_home', ['id' => $v['id']]);
                $u['count'] = $v['comments_count'];
                
                $u['user'] = ['username'=>$v['nickname'] ?: $v['name'] ,'avatar'=>$v['user_img']];
                
                $res['data'][] = $u;
            }
        } else {
            $res = ['status' => 0, 'msg' =>'no reply'];
        }
        return json($res);
    }

    /**
     * 获取用户评论列表
     *
     * @param integer $id
     * @return void
     */
    public function getUserCommentList1(int $id) {
        $userCommList = $this::field('id,user_id,create_time,article_id,content')
        ->with(['article' => function($query){
            $query->withField('id,title,create_time')->where(['delete_time'=>0,'status' => 1]);
        }])
        ->where(['user_id' => $id,'status' => 1])
        ->append(['url'])
        ->order(['create_time' => 'desc'])
        //->cache(3600)
        ->select()
        ->toArray();
        
        return $userCommList;
    }

    /**
     * 获取用户评论列表
     *
     * @param integer $id
     * @return void
     */
    public function getUserCommentList(int $id) {
        $userCommList = Article::field('Article.id,title,Article.create_time')->hasWhere('comments',['status'=>1,'delete_time'=>0])
        ->with(['comments' => function($query) use($id){
            $query->withField('id,content')->where(['user_id'=>$id,'delete_time'=>0,'status' => 1]);
        }])
        ->append(['url'])
        ->order(['create_time' => 'desc'])
        //->cache(3600)
        ->select()
        ->toArray();
        
        return $userCommList;
    }

    public function getCommentList(array $where, int $page = 1, int$limit = 10)
    {
        return $this->field('id,article_id,user_id,content,status,create_time')
            ->with([
                'user'=> function($query){
                    $query->field('id,name,user_img');
                },
                'article' => function($query) {
                $query->field('id,title');
                }
            ])
            ->where($where)
            ->order(['create_time' => 'desc'])
            ->paginate([
                    'list_rows' => $limit,
                    'page' => $page
                ])
            ->toArray();

    }

    // 获取url
    public function getUrlAttr($value,$data)
    {
        // dump($data);
        if(config('taoler.url_rewrite.article_as') == '<ename>/') {
            
            $article = Article::field('id,cate_id')->with(['cate' => function($query){
                $query->withField('id,ename')->where(['status' => 1]);
            }])->find($data['article_id']);
            
            return (string) url('detail',['id' => $data['article_id'],'ename'=>$article->cate->ename]);
        } else {
            return (string) url('detail',['id' => $data['id']]);
        }
    }
	
}