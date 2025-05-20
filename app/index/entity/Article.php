<?php
declare (strict_types = 1);

namespace app\index\entity;


use Exception;
use think\db\Query;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Session;
use app\common\lib\IdEncode;
use think\facade\Route;
use app\common\entity\BaseEntity;

class Article extends BaseEntity
{

    // 新的数量, 数据介于两表之间分量时使用
    protected static $newLimit;
    // 当前分页数据偏移量
    protected static $offset;
    // 当前用到的数据总和
    protected static $currentTotalNum = 0;

    /**
     * 添加
     * @param array $data
     * @return int|string|array
     */
	public function add(array $data)
	{
        $this->cate_id  = $data['cate_id'];
        $this->user_id  = $data['user_id'];
        $this->title    = $data['title'];
        $this->content  = $data['content'];
        $this->keywords = $data['keywords'];

        if(isset($data['status'])) {
            $this->status   = $data['status'];
        }
        
        $this->description  = $data['description'];
       
        $this->media = empty($data['media']) ? [
            'images' => [],
            'videos' => [],
            'audios' => []
        ] : $data['media'];
        
        $this->flags = empty($data['flags']) ? [
            'is_top'    => '0',
            'is_good'   => '0',
            'is_wait'   => '0',
        ] : $data['flags'];

        $result = $this->save();
        
        if(!$result) {
            throw new Exception('save error');
        }

        return ['id'=> $this->id];
	}

    /**
     * 编辑
     *
     * @param array $data
     * @return bool
     */
	public function edit(array $data): bool
	{
		$result = $this->save($data);

		if(!$result) {
			throw new Exception('edit error');
		}

        return true;
	}

    /**
     * 选和单选删除
     * @param array $ids
     * @return bool
     * @throws Exception
     */
    public function remove(array $ids): bool
    {
        try {
            foreach($ids as $id){
                $this->setSuffix(self::byIdGetSuffix($id));
                $article = $this->find($id);
                $article->together(['comments'])->delete();
                $article->delete();
            }
            
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return true;
    }

    /**
     * 置顶推荐文章
     *
     * @param integer $num 数量
     * @return array
     */
    public function getTops(int $num = 5): array
    {
        return Cache::remember('top_article', function() use($num) {

            $datas = [];
            // type = 1为置顶推荐文章
            $articleIds = Db::name('article_flag')->field('article_id')->where('type', 1)->limit($num)->select();

            $sufsAids = [];
            foreach($articleIds as $v){
                $key = self::byIdGetSuffix($v['article_id']);
                $sufsAids[$key][] = $v['article_id'];
            }

            foreach($sufsAids as $k => $v) {
                $data = $this->field('id,title,cate_id,user_id,create_time,pv,has_image,has_video,has_audio,media,comments_num,flags')
                ->suffix($k)
                ->with([
                    'cate' => function (Query $query) {
                        $query->field('id,catename,ename');
                    },
                    'user' => function (Query $query) {
                        $query->field('id,name,nickname,user_img');
                    }
                ])
                ->whereIn('id', $v)
                ->order('id', 'desc')
                // ->append(['url','master_pic'])
                ->append(['url'])
                ->select()
                ->toArray();

                $datas = array_merge($datas, $data);
            }

            return $datas;
            
        }, 600);
    }

    /**
     * 热评
     * @param int $num
     * @return array
     * @throws \Throwable
     */
    public function getHotComments(int $num = 10): array
    {

        $hots = Cache::remember('hot_comments', function() use($num){

            $comment = Db::name('comment')
            ->alias('c')
            ->field('c.article_id, count(*) as count')
            // ->join('article a', 'c.article_id = a.id')
            // ->whereMonth('c.create_time')
            // ->where(['c.status' => 1, 'c.delete_time' => 0])
            // ->where(['a.status' => 1, 'a.delete_time' => 0])
            ->group('c.article_id')
            ->order('count', 'desc')
            ->limit($num)
            ->select()
            ->toArray();

            $idArr = array_column($comment, 'article_id');
            // halt($idArr);
            $datas = [];
            if(!empty($idArr)) {
                $arr = self::getSfxKeyIdValueArrByIdArr($idArr);
                // halt($arr);
                foreach($arr as $suffix => $id) {
                    // 评论数
                    $data = $this->field('id,cate_id,title,create_time,comments_num')
                    ->suffix($suffix)
                    ->whereIn('id', $id)
                    ->where('delete_time', 0)
                    ->where('status', 1)
                    ->order('comments_num','desc')
                    ->append(['url'])
                    ->select()
                    ->toArray();

                    $datas = array_merge($datas, $data);
                }
            }

            return $datas;
        }, 360);

        return $hots;
    }

    /**
     * 阅读排行
     * @param int $num
     * @return array
     * @throws \Throwable
     */
    public function getHotPvs(int $num = 10): array
    {

        $hotPvs = Cache::remember('hot_pvs', function() use($num){

            $suffixArr = self::getSubTablesSuffix();
            $suffixArr[] = '';
            // halt($suffixArr);
            $count = count($suffixArr);
            $datas = [];
            
            for($i = 0; $i < $count; $i++) {
                // 评论数
                $data = $this->field('id,cate_id,title,pv,create_time,comments_num')
                ->suffix($suffixArr[$i])
                ->where('delete_time', 0)
                ->where('status', 1)
                ->order('pv', 'desc')
                ->limit($num)
                ->append(['url'])
                ->select()
                ->toArray();

                $datas = array_merge($datas, $data);

                $total = count($datas);
                if($total >= $num) {
                    break;
                }
            }
            

            return $datas;
        }, 360);

        return $hotPvs;
    }

    /**
     * 精华文章
     * @param int $num
     * @return array
     * @throws \Throwable
     */
    public function getGoods(int $num = 10): array
    {
        $goods =  Cache::remember('goods', function() use($num){

            $datas = [];
            $articleIds = Db::name('article_flag')
            ->field('article_id')
            ->where('type', 2)
            // ->whereMonth('create_time')
            ->limit($num)
            ->select();

            $articleArr = $articleIds->toArray();

            $idArr = array_column($articleArr, 'article_id');

            $arr = self::getSfxKeyIdValueArrByIdArr($idArr);

            foreach($arr as $k => $v){
                
                $data = $this->field('id,cate_id,title,create_time,comments_num')
                ->suffix($k)
                ->whereIn('id', $v)
                ->where('status', '1')
                ->append(['url'])
                ->select()
                ->toArray();

                $datas = array_merge($datas, $data);
            }

            return $datas;
        
        }, 3600);

        return $goods;
    }

    /**
     * 获取首页文章列表
     * @param int $num
     * @return array
     * @throws \Throwable
     */
    public function getIndexs(int $num = 10): array
    {
        $indexs = Cache::remember('idx_article', function() use($num){
            
            $map = self::getSuffixMap(['status' => 1], Article::class);

            $field = 'id,title,cate_id,user_id,content,description,pv,has_image,has_video,has_audio,create_time,media,comments_num,flags';
            // 判断是否有多个表
            if($map['tableCount'] > 1) {

                // 分表中$num数够
                if($map['countArr'][0] >= $num) {
                    $data = $this->suffix($map['tableSuffixArr'][0])->field($field)
                        ->with([
                        'cate' => function(Query $query){
                            $query->field('id,catename,ename,tpl');
                        },
                        'user' => function(Query $query){
                            $query->field('id,name,nickname,user_img');
                        } ])
                        ->where('status', '1')
                        ->order('id','desc')
                        ->append(['enid'])
                        // ->append(['url','master_pic'])
                        ->append(['url'])
                        ->limit($num)
                        ->select()
                        ->toArray();

                } else {
                    // 第一个分表 数量不够 取第二个分表数
                    $data = $this->suffix($map['tableSuffixArr'][0])->field($field)
                        ->with([
                        'cate' => function(Query $query){
                            $query->field('id,catename,ename,tpl');
                        },
                        'user' => function(Query $query){
                            $query->field('id,name,nickname,user_img');
                        } ])
                        ->where('status', '1')
                        ->order('id','desc')
                        ->append(['enid'])
                        // ->append(['url','master_pic'])
                        ->append(['url'])
                        ->limit($map['countArr'][0])
                        ->select()
                        ->toArray();

                    $data1 = $this->suffix($map['tableSuffixArr'][0])
                        ->field($field)
                        ->with([
                        'cate' => function(Query $query){
                            $query->field('id,catename,ename,tpl');
                        },
                        'user' => function(Query $query){
                            $query->field('id,name,nickname,user_img');
                        } ])
                        ->where('status', '1')
                        ->order('id','desc')
                        ->append(['enid'])
                        // ->append(['url','master_pic'])
                        ->append(['url'])
                        ->limit($num - $map['countArr'][0])
                        ->select()
                        ->toArray();

                    $data = array_merge($data, $data1);
                }
            } else {
                // 单表
                $data = $this->field($field)
                    ->with([
                    'cate' => function(Query $query){
                        $query->field('id,catename,ename,tpl');
                    },
                    'user' => function(Query $query){
                        $query->field('id,name,nickname,user_img');
                    } ])
                    ->where('status', '1')
                    ->order('id','desc')
                    ->append(['enid'])
                    // ->append(['url','master_pic'])
                    ->append(['url'])
                    ->limit($num)
                    ->select()
                    ->toArray();
            }

            return $data;
		}, 120);

        return $indexs;
    }

    /**
     * 获取详情
     * @param int $id 文章id
     * @return mixed
     * @throws \Throwable
     */
    public function getDetail(int $id)
    {
        $detail = Cache::remember('article_'.$id, function() use($id){
            $this->setSuffix(self::byIdGetSuffix($id));
            //查询文章
            try{
                return $this->field('id,title,content,status,cate_id,user_id,is_comment,pv,keywords,description,create_time,update_time,comments_num,flags')
                ->where('id', $id)
                ->where('status', '1')
                ->with([
                    'cate' => function(Query $query){
                        $query->field('id,catename,ename,tpl');
                    },
                    'user' => function(Query $query){
                        $query->field('id,name,nickname,user_img,area_id,vip,city');
                    }
                ])
                ->append(['url'])
                ->findOrFail();
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
            
        }, 600);

        return $detail;
    }

    /**
     * 上一篇
     *
     * @param integer $id 文章id
     * @param integer $cid 文章分类id
     * @return array
     */
    public function getPrev(int $id, int $cid): array
    {
        $this->setSuffix(self::byIdGetSuffix($id));

        $prev = [];

        $prevId = $this::where('id', '>=', $id + 1) // >= <= 条件可以使用索引
        ->where([
            ['cate_id', '=', $cid],
            ['status', '=',1]
        ])
        ->order('id asc')
        ->value('id');

        if(!is_null($prevId)) {
            $prev[] = $this::field('id,title,cate_id')->append(['url'])->find($prevId)->toArray();
        } else {
            $prev[] = ['title' => '前面没有了', 'url' => 'javascript:void(0);'];
        }

        return $prev;
    }

    /**
     * 下一篇
     *
     * @param integer $id
     * @param integer $cid
     * @return array
     */
    public function getNext(int $id, int $cid): array
    {
        $this->setSuffix(self::byIdGetSuffix($id));

        $next = [];

        $nextId = $this::where('id', '<=', $id - 1)
        ->where([
            ['cate_id', '=', $cid],
            ['status', '=',1]
        ])
        ->order('id desc')
        ->value('id');

        if(!is_null($nextId)) {
            $next[] = $this->field('id,title,cate_id')->append(['url'])->find($nextId)->toArray();
        } else {
            $next[] = ['title' => '后面没有了', 'url' => 'javascript:void(0);'];
        }

        return $next;
    }

    /**
     * 标签
     *
     * @param integer $id 文章id
     * @return array
     */
    public function getTags(int $id): array
    {
        return Cache::remember('tags_'.$id, function() use($id){
            $tagIdArr = Taglist::where('article_id', $id)->cache(true)->column('tag_id');
            $tags = Tag::field('name,ename')
            ->whereIn('id', $tagIdArr)
            ->append(['url'])
            ->select()
            ->toArray();
            
            return $tags;
        }, 1800);
    }


    /**
     * 相关文章
     *
     * @param integer $id 文章id
     * @param integer $limit 数量
     * @return array
     */
    public static function getRelationArticle(int $id, int $limit = 5): array
    {
        return Cache::remember('rela_'.$id, function() use($id,$limit) {

            $tagId = Taglist::where('article_id', $id)->value('tag_id');

            $articleIdArr = Taglist::where('tag_id', $tagId)
            ->where('article_id','<>', $id)
            ->limit($limit)
            ->column('article_id');

            $data = [];
            if(count($articleIdArr)) {
                foreach($articleIdArr as $id) {
                    $article = self::suffix(self::byIdGetSuffix($id))
                    ->with(['cate' => function($query) {
                        $query->field('id,catename');
                    }])
                    ->field('id,title,cate_id,pv,create_time,description')
                    ->where('id', $id)
                    ->append(['url'])
                    ->find();

                    if(!is_null($article)) {
                        $article['hasImg'] = $article['has_image'] > 0 ? true : false;
                        $article['time'] = $article['create_time'];
                        $article['cate_name']   = $article['cate']['catename'];
                        $article['desc']    = $article['description'];

                        $data[] = $article;
                    }
                }
            }
            
            return $data;
        }, 3600);
    }

    /**
     * 文章被点赞列表
     *
     * @param integer $id
     * @return array
     */
    public function getArticleZanList(int $id): array
    {
        return Cache::remember('zanlist_'.$id, function() use($id){
            $data = [];
            $uidArr = UserZan::where('article_id', $id)
            ->where('type', 1)
            ->column('user_id');
            $count = count($uidArr);
            if($count) {
                $data = User::field('user_img as avatar,name')
                ->whereIn('id', $uidArr)
                ->select()
                ->toArray();
            }

            return ['count' => $count, 'data' => $data];
        }, 3600);
    }

    /**
     * 相关文章(标签)
     * 相同标签文章，不包含自己
     * @param [type] $tagId
     * @param [type] $limit
     * @return void
     */
    public function getRelationTags($tagId, $id, $limit)
    {
        $allTags = Cache::remember("relation_tag_post_{$tagId}_{$id}", function() use($tagId,$id,$limit) {

            $arrId = Taglist::field('article_id')
            ->where([
                ['tag_id', '=', $tagId],
                ['article_id','<>',$id]
            ])
            ->order('id', 'desc')
            ->limit($limit)
            ->select();

            $tags = $this->field('id,cate_id,user_id,has_image,title,create_time,pv')
            ->whereIn('id', $arrId)
            ->where('status', '1')
            ->with([
                'user' => function($query){
                    $query->field('id,name,user_img');
                },'cate' => function($query){
                    $query->field('id,catename');
                }
            ])
            ->order('pv desc')
            ->append(['url'])
            ->select()
            ->toArray();
          
            $tagsArr = [];
            if(count($tags)) {
                foreach($tags as $v) {
                    $tagsArr[] = [
                        'id'        => $v['id'],
                        'hasImg'    => $v['has_image'] > 0 ? true : false,
                        'img'       => ($v['has_image'] > 0 && isset($v['media']['images'])) ? $v['media']['images'][0] : '',
                        'title'     => $v['title'],
                        'desc'      => $v['description'],
                        'auther'    => $v['user']['name'],
                        'cate_name' => $v['cate']['catename'],
                        'pv'        => $v['pv'],
                        'time'      => date('Y-m-d',strtotime($v['create_time'])),
                        'url'       => $v['url']
                    ];
                }
            }

            return $tagsArr;
        });

        return $allTags;
    }

    /**
     * 分类数据
     * @param string $ename
     * @param string $type  all\top\hot\jie 分类类型
     * @param int $page
     * @return mixed
     * @throws \Throwable
     */
    public function getCateList(string $ename, string $type, int $page = 1, int $limit = 15)
    {
        $where = [];
        $cateId = Category::where('status', 1)->where('ename', $ename)->value('id');

        if(!is_null($cateId)){
            $where[] = ['cate_id' ,'=', $cateId];
        }

        $where[] = ['status', '=', 1];

        switch ($type) {
            //查询文章,15个分1页
            case 'end':
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
            return $this::where($where)->count();
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

                $list =  Article::field('id,cate_id,user_id,title,content,description,create_time,pv,has_image,has_video,has_audio,comments_num,flags')
                ->with([
                    'cate' => function(Query $query) {
                        $query->field('id,catename,ename');
                    },
                    'user' => function(Query $query){
                        $query->field('id,name,nickname,user_img,vip');
                    }
                ])
                ->whereIn('id', $idArr)
                ->order('id', 'desc')
                ->select()
                ->append(['url'])
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

    // 获取用户发帖列表
    public function getUserArtList(int $id) {
        $userArtList = Cache::remember('user_recently_post_'.$id, function() use($id) {
            return $this::field('id,cate_id,title,create_time,pv')
            ->with([
                'cate' => function($query){
                    $query->where(['status'=>1])->field('id,ename');
                }
            ])
            ->where(['user_id' => $id, 'status' => 1])
            ->order('id','desc')
            ->append(['url'])
            ->limit(15)
            ->select()
            ->toArray();
        });
        
        return $userArtList;
    }

    // 获取搜索文章
    public function getSearchKeyWord(string $keywords)
    {
        //全局查询条件
        $map = [];
        
        //条件1：
        $map[] = ['status','=',1]; //这里等号不能省略

        if(!empty($keywords)){
            //条件2
            $map[] = ['title','like','%'.$keywords.'%'];
            $res = Article::where($map)
            ->withCount('comments')
            ->order('id','desc')
            ->append(['url'])
            ->paginate(10);
            
            return $res;
        }
    }

    // 获取所有帖子内容
    public function getList(array $where, int $limit, int $page)
    {
        return $this::field('id,user_id,cate_id,title,content,is_comment,status,update_time,comments_num,flags')
        ->with([
            'user' => function($query){
                $query->field('id,name,user_img');
            },
            'cate' => function($query){
                $query->field('id,ename,catename');
            }
        ])
        ->where(['status' => 1])
        ->where($where)
        ->order('id', 'desc')
        ->paginate([
            'list_rows' => $limit,
            'page' => $page
        ])->toArray();
    }

    // 获取admin应用所有帖子状态内容
    public function getAllStatusList(array $data, int $limit, int $page)
    {
        $where = [];
        if (!empty($data['sec'])) {
            switch ($data['sec']) {
                case '1':
                    $where[] = ['status', '=', 1];
                    break;
                case '2':
                    $where[] = ['is_top', '=', 1];
                    break;
                case '3':
                    $where[] = ['is_hot', '=', 1];
                    break;
                case '4':
                    $where[] = ['is_reply', '=', 1];
                    break;
                case '5':
                    $where[] = ['status', '=', -1];
                    break;
                case '6':
                    $where[] = ['status', '=', 0];
                    break;
            }
        }
        unset($data['sec']);

        if(!empty($data['id'])){
            $where[] = ['id', '=', $data['id']];
        }

        if(!empty($data['cate_id'])){
            $where[] = ['cate_id', '=', $data['cate_id']];
        }

        if(!empty($data['name'])){
            $userId = User::where('name',$data['name'])->value('id');
            $where[] = ['user_id', '=', $userId];
        }

        if(!empty($data['title'])){
            $where[] = ['title', 'like', '%'.$data['title'].'%'];
        }
        
        $count = $this->where($where)->cache(true)->count();

        // 默认排序
        $order = ['id' => 'desc'];

        if($page === 1) {
            // 第一页定位
            if(count($where)) {
                $maxId = (int)$this->where($where)->max('id');
            } else {
                $maxId = $this->order('id', 'desc')->value('id');
            }
            $where[] = ['id', '<=', $maxId];
        } else {
            // 非第一页，可以获取前分页标记
            $opage = Session::get('page');

            switch($page) {
                // next
                case $page > $opage['opg']:
                    $where[] = ['id', '<=', $opage['lid'] - 1];
                    break;
                // up
                case $page < $opage['opg']:
                    $where[] = ['id', '>=', $opage['fid'] + 1];
                    $order = ['id' => 'asc']; // 向上翻页时正序
                    break;
            }
        }

        $data = $this::field('id,user_id,cate_id,title,description,is_comment,status,update_time,comments_num')
        ->with([
             'user' => function($query){
                 $query->field('id,name,user_img');
             },
             'cate' => function($query){
                 $query->field('id,ename,catename');
             }
         ])
        ->where($where)
        ->order($order)
        ->limit($limit)
        ->select()
        ->toArray();

        // 向上翻页反转
        if($page != 1 && $page < $opage['opg']) {
            $data = array_reverse($data);
        }

        if($count) {
            // 翻页定位
            Session::set('page',['opg' => $page, 'fid' => $data[0]['id'], 'lid' => end($data)['id']]);
        }

        return ['data' => $data, 'count' => $count];
    }

    // 获取admin应用所有帖子状态内容
    public function getFilterList(array $data, int $page = 1, int $limit = 15)
    {
        $where = [];
        if (!empty($data['sec'])) {
            switch ($data['sec']) {
                case '1':
                    $where[] = ['status', '=', 1];
                    break;
                case '2':
                    $where[] = ['flags->is_top', '=', 1];
                    break;
                case '3':
                    $where[] = ['flags->is_good', '=', 1];
                    break;
                case '4':
                    $where[] = ['is_comment', '=', 1];
                    break;
                case '5':
                    $where[] = ['status', '=', -1];
                    break;
                case '6':
                    $where[] = ['status', '=', 0];
                    break;
            }
        }
        unset($data['sec']);

        if(!empty($data['id'])){
            $where[] = ['id', '=', $data['id']];
        }

        if(!empty($data['cate_id'])){
            $where[] = ['cate_id', '=', $data['cate_id']];
        }

        if(!empty($data['name'])){
            $userId = User::where('name',$data['name'])->value('id');
            $where[] = ['user_id', '=', $userId];
        }

        if(!empty($data['title'])){
            $where[] = ['title', 'like', '%'.$data['title'].'%'];
        }
        
        // 单个分表统计数 倒叙
        $countArr = [];
        // 数据总和
        $totals = 0;
        // 得到所有的分表后缀 倒叙排列
        $tableSuffixArr = self::getSubTablesSuffix('article');
        // 主表没有后缀，添加到分表数组中
        $tableSuffixArr[] = '';
        // 表综合
        $tableCount = count($tableSuffixArr);

        if($tableCount) {
            foreach($tableSuffixArr as $sfx) {
                $total = Article::suffix($sfx)->where($where)->count();
                $countArr[] = $total;
                $totals += $total;
            }
        }

        $map = [
            'countArr'    => $countArr,
            'totals'    => $totals,
            'tableSuffixArr' => $tableSuffixArr,
            'tableCount' => $tableCount
        ];
        // halt($map);
        // 总共页面数
        $lastPage = (int) ceil($map['totals'] / $limit); // 向上取整
        
        $datas = [];
        if($map['totals']) {

            if($page > $lastPage) {
                throw new Exception('no data');
            }

            
            // 最大偏移量
            $maxNum = $page * $limit;
            // 开始时的偏移量
            self::$offset = ($page - 1) * $limit;
            // newLimit首次=limit, newLimit 在数据介于两表之间时分量使用
            self::$newLimit = $limit;

            $field = 'id,cate_id,user_id,title,is_comment,pv,status,create_time,update_time,comments_num,flags';

            for($i = 0; $i < $map['tableCount']; $i++) {

                self::$currentTotalNum += $map['countArr'][$i];

                // 1.可以完全取到 在第一组分表中就可以完全查询到
                if((self::$currentTotalNum - $maxNum) >= 0){
                    // echo 123;
                
                    $articles = $this->suffix($map['tableSuffixArr'][$i])
                    ->field('id')
                    ->where($where)
                    ->order('id', 'desc')
                    ->limit(self::$offset, self::$newLimit)
                    ->select();

                    $ids = $articles->toArray();
                    $idArr = array_column($ids, 'id');

                    // halt($idArr);

                    $list =  $this->suffix($map['tableSuffixArr'][$i])
                    ->field($field)
                    ->whereIn('id', $idArr)
                    ->with([
                        'user' => function(Query $query){
                            $query->field('id,name,nickname,user_img,vip');
                        },
                        'cate' => function(Query $query){
                            $query->field('id,ename,catename');
                        }
                    ])
                    // ->withCount(['comments'])
                    ->order('id', 'desc')
                    ->append(['aurl'])
                    ->select()
                    ->toArray();
                    
                    $datas = array_merge($datas, $list);
                   
                    break;
                } 

                // 2.数据介于2表之间 第一组和第二组各取部分数据
                if((self::$currentTotalNum - $maxNum) < 0 && ($maxNum - self::$currentTotalNum - $limit) < 0 ) {
                    // echo 234;

                    $articles = $this->suffix($map['tableSuffixArr'][$i])
                    ->field('id')
                    ->where($where)
                    ->order('id', 'desc')
                    ->limit(self::$offset, self::$newLimit)
                    ->select();

                    $ids = $articles->toArray();
                    $idArr = array_column($ids, 'id');

                    $list =  $this->suffix($map['tableSuffixArr'][$i])
                    ->field($field)
                    ->whereIn('id', $idArr)
                    ->with([
                        'user' => function(Query $query){
                            $query->field('id,name,nickname,user_img,vip');
                        },
                        'cate' => function(Query $query){
                            $query->field('id,ename,catename');
                        }
                    ])
                    ->order('id', 'desc')
                    ->append(['aurl'])
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

        }

        return [
            'total'         => $map['totals'],
            'per_page'      => $limit,
            'current_page'  => $page,
            'last_page'     => $lastPage,
            'data'          => $datas
        ];

    }


}