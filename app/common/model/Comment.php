<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-06-27 16:51:29
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \TaoLer\app\common\model\Comment.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Cache;

class Comment extends Model
{
	protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
	
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
	    $comments = $this::with(['user'])->where(['article_id'=>$id,'status'=>1])->order(['cai'=>'asc','create_time'=>'asc'])->paginate(['list_rows'=>10, 'page'=>$page]);
	    return $comments;
    }

    //回帖榜
    public function reply($num)
    {
        $user = Cache::get('user_reply');
        if(!$user){
            $user = User::field('id,user_img,name,nickname')
            ->withCount(['comments'=> function($query) {
                $query->where(['status'=>1]);
            }])
            ->order(['comments_count'=>'desc','last_login_time'=>'desc'])
            ->limit($num)
            ->select()
            ->toArray();
            Cache::set('user_reply',$user,180);
        }

        if(!empty($user)) {
            $res['status'] = 0;
            $res['data'] = array();
            foreach ($user as $key=>$v) {
                //$u['uid'] = (string) url('user/home',['id'=>$v['id']]);
                $u['uid'] = (string) \think\facade\Route::buildUrl('user_home', ['id' => $v['id']]);
                $u['count(*)'] = $v['comments_count'];
                if($v['nickname'])
                {
                    $u['user'] = ['username'=>$v['nickname'],'avatar'=>$v['user_img']];
                } else {
                    $u['user'] = ['username'=>$v['name'],'avatar'=>$v['user_img']];
                }
                $res['data'][] = $u;
            }
            return json($res);
        }
        
    }

    /**
     * 获取用户评论列表
     *
     * @param integer $id
     * @return void
     */
    public function getUserCommentList(int $id) {
        $userCommList = $this::field('id,user_id,create_time,delete_time,article_id,content')
        ->with(['article' => function(\think\model\Relation $query){
            $query->withField('id,title,cate_id,delete_time')->where(['status' => 1,'delete_time' => 0]);
        }])
        ->where(['user_id' => $id,'status' => 1,'delete_time'=>0])
        //->append(['url'])
        ->order(['create_time' => 'desc'])
        //->cache(3600)
        ->select()
        ->toArray();
        
        return $userCommList;
    }

    // 获取url
    public function getUrlAttr($value,$data)
    {
        if(config('taoler.url_rewrite.article_as') == '<ename>/') {
            $cate = Cate::field('id,ename')->find($data['article']['cate_id']);
            return (string) url('detail',['id' => $data['id'],'ename'=>$cate->ename]);
        } else {
            return (string) url('detail',['id' => $data['id']]);
        }
    }
	
}