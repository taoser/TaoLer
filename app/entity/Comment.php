<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2026-11-14 08:38:54
 * @LastEditors: TaoLer
 * @Description: 评论模型
 * @FilePath: \TaoLer\app\entity\Comment.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */

namespace app\entity;

use Exception;
use think\facade\Cache;

class Comment extends BaseEntity
{	

    /**
     * article comments
     *
     * @param integer $id article_id
     * @param integer $page
     * @param integer $limit
     * @return array
     */
    public function getComments(int $id, int|string $page = 1, int $limit = 15): array
    {
        if(is_string($page)){
            $page = (int) $page;
        }

        $datas = Cache::remember("comments_{$id}_{$page}", function() use($id, $page, $limit) {
            $data = [];
            $offset = ($page - 1) * $limit;
            $comments = self::field('id')
                ->withTrashed()
                ->where('article_id', $id)
                ->where('status', '1')
                ->order('id', 'desc')
                ->limit($offset, $limit)
                ->select();
    
            $ids = $comments->toArray();
            $idArr = array_column($ids, 'id');
    
            $count = count($idArr);
            
            if($count) {
                $data = self::whereIn('id', $idArr)
                ->with(['user'=>function($query){
                    $query->field('id,name,avatar,sign,city,vip');
                }])
                ->order(['cai'=>'asc','create_time'=>'asc'])
                ->append(['touser'])
                ->select()
                ->toArray();
            }

            return ['count' => $count, 'data' => build_tree($data)];
        }, 600);
        
        return $datas;
    }

    //回帖榜
    public function reply($num)
    {
        try {
            $user = User::field('id,avatar,name,nickname')
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
                
                $u['user'] = ['username'=>$v['nickname'] ?: $v['name'] ,'avatar'=>$v['avatar']];
                
                $res['data'][] = $u;
            }
        } else {
            $res = ['status' => -1, 'msg' =>'no reply'];
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
            $query->withField('id,title,create_time')->where('status',1)->whereNull('delete_time');
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
        $userCommList = Article::field('Article.id,title,Article.create_time')->hasWhere('comments',['status'=>1])
        ->with(['comments' => function($query) use($id){
            $query->withField('id,content')->where(['user_id'=>$id,'status' => 1]);
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
                    $query->field('id,name,avatar');
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

    /**
     * 获取用户评论列表
     *
     * @param int $uid 用户ID
     * @param int $page 页码
     * @param int $limit 每页数量   
     * @return array
     */
    public function getMyCommentList(int $uid, int $page = 1, int $limit = 20): array
    {
        $page = max(1, $page);
        $limit = max(1, min($limit, 100));

        $count = $this->where('user_id', $uid)->count();
        if($count === 0) {
            return ['count' => 0, 'data' => []];
        }

        $data = $this->with([
            'user'=> function($query) {
                    $query->field('id,name,avatar');
                },
                'article' => function($query) {
                    $query->field('id,title');
                }
            ])
            ->where('user_id', $uid)
            ->order(['create_time' => 'desc'])
            ->page($page, $limit)
            ->select()
            ->toArray();
        
        return ['count' => $count, 'data' => $data];

    }



}