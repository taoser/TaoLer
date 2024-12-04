<?php

namespace app\common\model;

use Exception;
use think\Model;
use think\db\Query;
use think\facade\Cache;
use think\facade\Session;
use think\facade\Db;
use Sqids\Sqids;

class Category extends BaseModel
{
    protected $name = 'cate';

    // 新的数量, 数据介于两表之间分量时使用
    protected static $newLimit;
    // 当前分页数据偏移量
    protected static $offset;
    // 当前用到的数据总和
    protected static $currentTotalNum = 0;



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
    public static function getArticlesByCategoryEname1(string $ename, int $page = 1, string $type = 'all', int $limit = 15)
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


    public static function getArticlesByCategoryEname2(string $ename, int $page = 1, string $type = 'all', int $limit = 15)
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

    // 分类文章
    public static function getArticlesByCategoryEname(string $ename, int $page = 1, string $type = 'all', int $limit = 15)
    {

        // 查询条件
        $where = [];
        // 数据
        $data = [];

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

// $limit = 5;
// $page = 3;

        // $m = self::getSuffixMap(['status' => 1]);
        // halt($m);

        // 文章分类总数
        $map = Cache::remember("cate_count_{$ename}_{$type}", function() use($where){


            return self::getSuffixMap($where, Article::class);
            
            // 单个分表统计数 倒叙
            $counts = [];
            // 数据总和
            $totals = 0;

            // 得到所有的分表后缀 倒叙排列
            $suffixArr = self::getSubTablesSuffix('article');
            // 主表没有后缀，添加到分表数组中
            $suffixArr[] = '';

            // 表综合
            $suffixCount = count($suffixArr);

            if($suffixCount) {
                foreach($suffixArr as $sfx) {
                    $total = Article::suffix($sfx)->where($where)->count();
                    $counts[] = $total;
                    $totals += $total;
                }
            }

            return [
                'counts'    => $counts,
                'totals'    => $totals,
                'suffixArr' => $suffixArr,
                'suffixCount' => $suffixCount
            ];
        });


        // 总共页面数
        $lastPage = (int) ceil($map['totals'] / $limit); // 向上取整
 
        if($map['totals']) {

            if($page > $lastPage) {
                throw new Exception('no data');
            }

            $data = Cache::remember("cateroty_{$ename}_{$type}_{$page}", function() use($where, $page, $limit, $map) {

                $datas = [];
                // 最大偏移量
                $maxNum = $page * $limit;
                // 开始时的偏移量
                self::$offset = ($page - 1) * $limit;
                // newLimit首次=limit, newLimit 在数据介于两表之间时分量使用
                self::$newLimit = $limit;


                for($i = 0; $i < $map['suffixCount']; $i++) {

                    self::$currentTotalNum += $map['counts'][$i];

                    // 1.可以完全取到 在第一组分表中就可以完全查询到
                    if((self::$currentTotalNum - $maxNum) >= 0){
                        // echo 123;
                    
                        $articles = Article::suffix($map['suffixArr'][$i])->field('id')->where($where)->order('id', 'desc')->limit(self::$offset, self::$newLimit)->select();
                        $ids = $articles->toArray();
                        $idArr = array_column($ids, 'id');

                        $list =  Article::suffix($map['suffixArr'][$i])->field('id,cate_id,user_id,title,content,description,title_color,create_time,is_top,is_hot,pv,jie,has_img,has_video,has_audio,read_type,art_pass')
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
                        ->append(['url'])
                        ->hidden(['art_pass'])
                        ->select()
                        ->toArray();
                        
                        $datas = array_merge($datas, $list);
                        break;
                    } 

                    // 2.数据介于2表之间 第一组和第二组各取部分数据
                    if((self::$currentTotalNum - $maxNum) < 0 && ($maxNum - self::$currentTotalNum - $limit) < 0 ) {
                        // echo 234;

                        $articles = Article::suffix($map['suffixArr'][$i])->field('id')->where($where)->order('id', 'desc')->limit(self::$offset, self::$newLimit)->select();
                        $ids = $articles->toArray();
                        $idArr = array_column($ids, 'id');

                        $list =  Article::suffix($map['suffixArr'][$i])->field('id,cate_id,user_id,title,content,description,title_color,create_time,is_top,is_hot,pv,jie,has_img,has_video,has_audio,read_type,art_pass')
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
                        ->append(['url'])
                        ->hidden(['art_pass'])
                        ->select()
                        ->toArray();
                        
                        $datas = array_merge($datas, $list);
                        
                        // 介于2表之间 第二张表分量从0开始
                        self::$offset = 0;
                        // 第二张表分量数
                        self::$newLimit = $page * $limit - self::$currentTotalNum;
            
                    }

                    // 3.第一组完全取不到 数据没有在第一组，刚好从第二组开头取, 只能从后面一组从0开始继续找 ，需要跳过当次循环
                    if($maxNum - self::$currentTotalNum - $limit == 0) {

                        // echo 345;

                        self::$offset = 0;
                    }

                    // 4.第一组完全取不到 且不是从第二组开头找，需要跳过当次循环
                    if((self::$currentTotalNum - $maxNum < 0) && ($maxNum - self::$currentTotalNum - $limit > 0) ) {

                        // echo 456;

                        // 第一组可分页面数
                        $p = (int) floor(self::$currentTotalNum  / self::$newLimit);
                        // 第一组余量数
                        $n = self::$currentTotalNum  % self::$newLimit;

                        // 第二组的偏移量
                        self::$offset = ($page - 1 - $p) * self::$newLimit - $n;
                    }               

                }          
               
                return $datas;

            }, 600);
        }

        if(config('taoler.id_status') === 1) {
            $sqids = new Sqids(config('taoler.id_alphabet'), config('taoler.id_minlength'));
            foreach($data as $k => $v) {
                $data[$k]['id'] = $sqids->encode([$v['id']]);
            }
        }

        return [
            'total' => $map['totals'],
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => $lastPage,
            'data' => $data
        ];

    }



}