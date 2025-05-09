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

namespace app\index\entity;

use Exception;
use think\facade\Cache;
use app\common\entity\BaseEntity;

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
                    $query->field('id,name,user_img,sign,city,vip');
                }])
                ->order(['cai'=>'asc','create_time'=>'asc'])
                ->append(['touser'])
                ->select()
                ->toArray();
            }

            return ['count' => $count, 'data' => getTree($data)];
        }, 600);
        
        return $datas;
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
            
            $article = Article::field('id,cate_id')
            ->with(['cate' => function($query){
                $query->withField('id,ename')->where(['status' => 1]);
            }])
            ->find($data['article_id']);

            return (string) url('detail', ['id' => $data['article_id'], 'ename' => $article->cate->ename]);
        } else {
            return (string) url('detail',['id' => $data['id']]);
        }
    }

    // 获取to_user_id
    public function getTouserAttr($value, $data)
    {
        if(isset($data['to_user_id'])) {
            return User::where('id', $data['to_user_id'])->value('name');
        }
        return '';
    }


    /**
     * 评论没有被删除正常显示
     * 评论被删除，但它下面有跟评时自身会显示为“评论已删除”，跟评会显示，无跟评且已删除则不显示
     * @param $value
     * @param $data
     * @return string
     */
    public function getContentAttr($value,$data)
    {
        if($data['delete_time'] == 0) {
            return $value;
        } else {
            if($this::getByPid($data['id'])) {
                return '<span style="text-decoration:line-through;">评论已删除</span>';
            } else {
                return '';
            }

        }
    }

}