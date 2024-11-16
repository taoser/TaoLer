<?php

namespace app\model\index;

use Exception;
use think\Model;
use think\db\Query;
use think\facade\Cache;
use think\facade\Session;


class Category extends Model
{
    protected $name = 'cate';

    //文章关联评论
	public function article()
	{
		return $this->hasMany(Article::class);
	}

    // 查询类别信息
	public static function getCateInfo(string $ename)
	{
		//
		return self::field('ename,catename,detpl,desc')->where('status', 1)->where('ename', $ename)->cache('cate_'.$ename, 600)->find();
	}


    // 分类文章
    public static function getArticlesByCategoryEname(string $ename, int $page = 1, string $type = 'all', int $limit = 15)
    {

        $where = [];
        $cateId = self::where('status', 1)->where('ename', $ename)->value('id');

        if(!is_null($cateId)){
            $where[] = ['cate_id' ,'=', $cateId];
        }

        $where[] = ['status', '=', 1];

        switch ($type) {
            //查询文章,15个分1页
            case 'jie':
                $where[] = ['jie','=', '1'];
                break;
            case 'hot':
                $where[] = ['is_hot','=', '1'];
                break;
            case 'top':
                $where[] = ['is_top' ,'=', '1'];
                break;
            case 'wait':
                $where[] = ['jie','=', '0'];
                break;
        }

        // 文章分类总数
        $count = (int) Cache::remember("cate_count_{$ename}_{$type}", function() use($where){
            return Article::where($where)->count();
        });

        $data = [];
// halt($count);
        // 总共页面数
        $lastPage = (int) ceil($count / $limit); // 向上取整
 
        if($count) {

            if($page > $lastPage) {
                throw new Exception('no data');
            }

            $data = Cache::remember("cateroty_{$ename}_{$type}_{$page}", function() use($where, $page, $limit) {
                $articles = Article::field('id')->where($where)->order('id', 'desc')->page($page, $limit)->select();
                $ids = $articles->toArray();
                $idArr = array_column($ids, 'id');

                $list =  Article::field('id,cate_id,user_id,title,content,description,title_color,create_time,is_top,is_hot,pv,jie,has_img,has_video,has_audio,read_type,art_pass')
                ->with([
                    'cate' => function(Query $query) {
                        $query->field('id,catename,ename');
                    },
                    'user' => function(Query $query){
                        $query->field('id,name,nickname,user_img,vip');
                    }
                ])
                ->withCount(['comments'])
                ->whereIn('id', $idArr)
                ->order('id', 'desc')
                ->select()
                ->append(['url'])
                ->hidden(['art_pass'])
                ->toArray();

                return $list;
                
            }, 600);
        }

        return [
            'total' => $count,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => $lastPage,
            'data' => $data
        ];

    }


    public static function getArticlesByCategoryEname1(string $ename, int $page = 1, string $type = 'all', int $limit = 15)
    {
        // $articles = Article::withJoin(['category' => function(Query $query) use($ename) {
        //     $query->where('category.ename', $ename);
        // }],'RIGHT')
        // ->where('article.status', 1)
        // ->hidden(['art_pass'])
        // ->select()->toArray();
       
        // $cateId = self::where('status', 1)->where('ename', $ename)->value('id');

        // $articles = Article::where('status', 1)
        // ->where('cate_id', $cateId)
        // ->hidden(['art_pass'])
        // ->select()->toArray();
 
        // $cate = $this->field('id')->where('ename', $ename)->find();

        // $articles = $cate->article()->field('id,title')->where(['status' => 1])->hidden(['art_pass'])->select();
    
        // return $articles;

        // ------------------------

        $where = [];
        $where[] = ['status', '=', 1];

        switch ($type) {
            //查询文章,15个分1页
            case 'jie':
                $where[] = ['jie','=', '1'];
                break;
            case 'hot':
                $where[] = ['is_hot','=', '1'];
                break;
            case 'top':
                $where[] = ['is_top' ,'=', '1'];
                break;
            case 'wait':
                $where[] = ['jie','=', '0'];
                break;
        }

        // ··································

        $cateId = self::where('status', 1)->where('ename', $ename)->value('id');

        if(!is_null($cateId)){
            $where[] = ['cate_id' ,'=', $cateId];
        }

        // 文章分类总数
        $count = (int) Cache::remember("cate_count_{$ename}_{$type}", function() use($where){
            return Article::where($where)->count();
        });

        // 总共页面数
        $lastPage = (int) ceil($count / $limit);

        if($page > $lastPage) {
            throw new Exception('no data');
        }

        $data = [];
 
        if($count) {
            $data = Cache::remember("cateroty_{$ename}_{$type}_{$page}", function() use($where, $page, $count, $limit) {
                
                // 默认排序
                $order = ['id' => 'desc'];
    
                if($page === 1) {
                    // 第一页定位
                    if(count($where)) {
                        // 有搜索条件时
                        $maxId = (int) Article::where($where)->max('id');
                    } else {
                        $maxId = Article::where('status', 1)->order('id', 'desc')->value('id');
                    }
    
                    $where[] = ['id', '<=', $maxId];
    
                } else {
                    // 非第一页，可以获取前分页标记
                    if(Session::has('category_opage')) {
                        $opage = Session::get('category_opage');

                        // next
                        if($page > $opage['opg']) {
                            $where[] = ['id', '<=', $opage['lid'] - 1];
                        }
        
                        // up
                        if($page < $opage['opg']) {
                            $where[] = ['id', '>=', $opage['fid'] + 1];
                            $order = ['id' => 'asc']; // 向上翻页时正序
                        }
                    }
                    
                }
    
                $articles =  Article::field('id,cate_id,user_id,title,content,description,title_color,create_time,is_top,is_hot,pv,jie,has_img,has_video,has_audio,read_type,art_pass')
                ->with([
                    'cate' => function(Query $query) {
                        $query->field('id,catename,ename');
                    },
                    'user' => function(Query $query){
                        $query->field('id,name,nickname,user_img,vip');
                    }
                ])
                ->withCount(['comments'])
                ->where($where)
                ->order($order)
                ->limit($limit)
                ->select()
                ->append(['url'])
                ->hidden(['art_pass'])
                ->toArray();
    
                // 向上翻页反转
                if($page !== 1 && $page < $opage['opg']) {
                    $articles = array_reverse($articles);
                }

                return $articles;
                
            }, 600);
        }

        // 翻页定位
        Session::set('category_opage', [
            'opg' => $page, //当前页
            'fid' => $data[0]['id'], // 第一ID
            'lid' => end($data)['id'] // 最后id
        ]);

        return [
            'total' => $count,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => $lastPage, // 向上取整
            'data' => $data
        ];


    }



}