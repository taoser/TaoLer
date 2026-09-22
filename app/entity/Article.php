<?php
declare (strict_types = 1);

namespace app\entity;

use think\db\Query;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Session;
use app\common\helper\IdEncode;
use app\common\service\ArticleService;
use app\common\strategy\ArticleValidation;
use app\common\strategy\DataValidationStrategy;
use app\common\strategy\AuthValidationStrategy;
use app\common\decorator\MainArticleProcessorDecorator;
use app\common\decorator\SensitiveWordFilter;
use app\common\decorator\WordsDesc;
use app\common\decorator\Media;
use app\common\observer\ObserverManager;
use app\common\observer\LogObserver;
use app\common\observer\TagObserver;
use app\common\observer\MailObserver;
use app\service\ArticleCache;
use app\exception\BusinessException;

class Article extends BaseEntity
{
    // 1. 字段常量管理（抽离到模型更佳）
    const ARTICLE_LIST_FIELDS = [
        'a.id', 'a.category_id', 'a.user_id', 'a.title', 'a.content', 'a.description',
        'a.create_time', 'a.pv', 'a.thumb', 'a.has_image', 'a.has_video', 'a.has_audio',
        'a.comments_num', 'a.flags'
    ];
    const CATE_RELATION_FIELDS = ['id', 'name', 'ename'];
    const USER_RELATION_FIELDS = ['id', 'name', 'nickname', 'avatar', 'vip'];

    // 新的数量, 数据介于两表之间分量时使用
    protected static int $newLimit = 100000;
    // 当前分页数据偏移量
    protected static int $offset = 0;
    // 当前用到的数据总和
    protected static int $currentTotalNum = 0;

    public function addData(array $data): Article
    {
        $articleServer = new ArticleService();
		
        // 校验策略
        $articleServer->setValidation(new ArticleValidation())
            ->addValidation(new DataValidationStrategy())
            ->addValidation(new AuthValidationStrategy())
            ->addValidation(new \app\common\strategy\PostValidationStrategy());

        // 装饰
        $articleServer->setDecorator(new MainArticleProcessorDecorator())
            ->addProcessor(new SensitiveWordFilter()) //违禁词过滤
            ->addProcessor(new WordsDesc()) //关键词描述
            ->addProcessor(new Media()) // 媒体处理
            ->addProcessor(new \app\common\decorator\Image()); // 图片处理
        // 观察者策略
        $articleServer->setObserverManager(new ObserverManager())
            ->addObserver(new LogObserver())
            ->addObserver(new MailObserver())
            ->addObserver(new TagObserver());

        return $articleServer->add($data, $this);
    }

    /**
     * 编辑文章
     * @param array $data 文章数据
     * @return Article 文章实体
     */
    public function editData(array $data): Article
    {
        $articleServer = new ArticleService();

        $article = $this->suffix(self::getSuffixById($data['id']))->find($data['id']);
 
        if(is_null($article)) {
            throw new BusinessException('文章不存在', 1, ['id' => $data['id']]);
        }

        $articleServer = new ArticleService();
            
        // 校验策略
        $articleServer->setValidation(new ArticleValidation())
            ->addValidation(new DataValidationStrategy())
            ->addValidation(new AuthValidationStrategy());

        // 装饰
        $articleServer->setDecorator(new MainArticleProcessorDecorator())
            ->addProcessor(new SensitiveWordFilter()) //违禁词过滤
            ->addProcessor(new WordsDesc()) //关键词描述
            ->addProcessor(new Media()) // 媒体处理
            ->addProcessor(new \app\common\decorator\Image()); // 图片处理

        // 观察者策略
        $articleServer->setObserverManager(new ObserverManager())
            ->addObserver(new TagObserver())
            ->addObserver(new MailObserver())
            ->addObserver(new LogObserver());

        $result = $articleServer->edit($data, $article);
        
        return $result;

    }

    /**
     * 删除
     * @param int $id 文章ID
     * @param int $uid 用户ID
     * @return bool
     * @throws BusinessException
     */
    public function deleteData(int $id, int $uid): bool
    {
        Db::startTrans();
        try {
            $this->setSuffix(self::getSuffixById($id));

            $article = $this->where('user_id', $uid)->find($id);
            if (is_null($article)) {
                throw new BusinessException('文章不存在', 1, ['id' => $id]);
            }
            $article->together(['comments'])->force()->delete();
            
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
        return true;
    }

    /**
     * 多选和单选删除
     * @param array $ids
     * @return bool
     * @throws BusinessException
     */
    public function remove(array $ids): bool
    {
        Db::startTrans();
        try {
            foreach ($ids as $id) {
                $this->setSuffix(self::getSuffixById($id));
                $article = $this->find($id);
                if (is_null($article)) {
                    throw new BusinessException('文章不存在', -1, ['id' => $id]);
                }
                $article->together(['comments'])->delete();
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
        return true;
    }

    /**
     * Flag文章列表
     * @param string $type 类型 top/index/good
     * @param integer $limit 数量
     * @return array
     */
    public function getFlagArticles(string $type, int $limit = 5): array
    {
        $types = [
            'top'   => 1,
            'index' => 2,
            'good'  => 3,
        ];

        if(!isset($types[$type])) {
            throw new BusinessException('type error');
        }

        // 获取缓存Flag文章列表
        $flagArticles = ArticleCache::getFlagArticles($type);
        if($flagArticles) {
            return $flagArticles;
        }

        $datas = [];
        // type = 1为置顶推荐文章
        $articleIds = Db::name('article_flag')->field('article_id')->where('type', $types[$type])->limit($limit)->select();

        $sufsAids = [];
        foreach($articleIds as $v){
            $key = self::getSuffixById($v['article_id']);
            $sufsAids[$key][] = $v['article_id'];
        }

        foreach($sufsAids as $suf => $ids) {
            $data = $this->suffix($suf)
            ->field('id,title,category_id,user_id,description,create_time,pv,thumb,has_image,has_video,has_audio,media,comments_num,flags')
            ->with([
                'category' => function (Query $query) {
                    $query->field('id,name,ename');
                },
                'user' => function (Query $query) {
                    $query->field('id,name,nickname,avatar');
                }
            ])
            ->whereIn('id', $ids)
            ->order('id', 'desc')
            // ->append(['url','master_pic'])
            ->append(['url'])
            ->select()
            ->toArray();

            $datas = array_merge($datas, $data);
        }
        
        // 缓存Flag文章列表
        ArticleCache::setFlagArticles($type, $datas);

        return $datas;
    }

    /**
     * 置顶推荐列表
     *
     * @param integer $limit 数量
     * @return array
     */
    public function getTops(int $limit = 5): array
    {
        return $this->getFlagArticles('top', $limit);
    }

    /**
     * 获取首页列表
     * @param int $limit
     * @return array
     * @throws \Throwable
     */
    public function getIndexs(int $limit = 10): array
    {
        return $this->getFlagArticles('index', $limit);
    }

    /**
     * 精华文章
     * @param int $limit
     * @return array
     * @throws \Throwable
     */
    public function getGoods(int $limit = 10): array
    {
        return $this->getFlagArticles('good', $limit);
    }


    /**
     * 热评
     * @param int $limit
     * @return array
     * @throws \Throwable
     */
    public function getHotComments(int $limit = 10): array
    {

        $hots = Cache::remember('hot_comments', function() use($limit){

            $comment = Db::name('comment')
            ->alias('c')
            ->field('c.article_id, count(*) as count')
            // ->join('article a', 'c.article_id = a.id')
            // ->whereMonth('c.create_time')
            // ->where(['c.status' => 1, 'c.delete_time' => 0])
            // ->where(['a.status' => 1, 'a.delete_time' => 0])
            ->group('c.article_id')
            ->order('count', 'desc')
            ->limit($limit)
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
                    $data = $this::with(['category'=> function($query) {
                        $query->field('id,name,ename');
                    }])->field('id,category_id,title,create_time,comments_num')
                    ->suffix($suffix)
                    ->whereIn('id', $id)
                    ->whereNull('delete_time')
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
     * @param int $limit
     * @return array
     * @throws \Throwable
     */
    public function getHotPvs(int $limit = 10): array
    {

        $hotPvs = Cache::remember('hot_pvs', function() use($limit){

            $suffixArr = self::getSubTablesSuffix();
            $suffixArr[] = '';
            // halt($suffixArr);
            $count = count($suffixArr);
            $datas = [];
            
            for($i = 0; $i < $count; $i++) {
                // 评论数
                $data = $this::with(['category'=> function($query) {
                    $query->field('id,name,ename');
                }])
                ->field('id,category_id,title,pv,create_time,comments_num')
                ->suffix($suffixArr[$i])
                ->whereNull('delete_time')
                ->where('status', 1)
                ->order('pv', 'desc')
                ->limit($limit)
                ->append(['url'])
                ->select()
                ->toArray();

                $datas = array_merge($datas, $data);

                $total = count($datas);
                if($total >= $limit) {
                    break;
                }
            }
            

            return $datas;
        }, 360);

        return $hotPvs;
    }

    /**
     * 详情信息
     * @param int $id 文章id
     * @return mixed
     * @throws \Throwable
     */
    public function getInfo(int $id)
    {
        // 设置分表后缀
        $this->setSuffix(self::getSuffixById($id));

        $detail =  $this->field('id,title,content,category_id,keywords,description')
        ->where('id', $id)
        ->append(['tagid'])
        ->find();

        if(is_null($detail)) {
            throw new BusinessException('内容不存在', 404);
        }

        return $detail;
    }

    /**
     * 获取详情
     * @param int|string $id 文章id
     * @return mixed
     * @throws \Throwable
     */
    public function getDetail(int|string $id)
    {
        // 如果id是加密的，解密后获取id值
        $id = IdEncode::decode($id);
        // 设置分表后缀
        $this->setSuffix(self::getSuffixById($id));
        // 从缓存中获取文章详情
        $detail = ArticleCache::get($id);
 
        if(is_null($detail)) {

            $detail =  $this->field('pv,id,title,content,status,category_id,user_id,forbid_comment,keywords,description,create_time,update_time,comments_num,flags')
            ->where('id', $id)
            ->with([
                'category' => function(Query $query){
                    $query->field('id,name,ename,tpl');
                },
                'user' => function(Query $query){
                    $query->field('id,name,nickname,avatar,area_id,vip,city');
                }
            ])
            ->append(['url'])
            ->find();

            if(is_null($detail)) {
                throw new BusinessException('内容不存在', 404);
            }

            if($detail['status'] == 0) {
                throw new BusinessException('内容待审核', 2);
            }

            // 缓存文章详情
            ArticleCache::set($id, $detail);

        }

        // 步增pv
        $detail->setInc('pv', 1);

        $detail->pv = $this->where('id', $id)->value('pv');

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
        $this->setSuffix(self::getSuffixById($id));

        $prev = [];

        $prevId = $this::with(['category'=> function($query) {
            $query->field('id,name,ename');
        }])->where('id', '>=', $id + 1) // >= <= 条件可以使用索引
        ->where([
            ['category_id', '=', $cid],
            ['status', '=',1]
        ])
        ->order('id asc')
        ->value('id');

        if(!is_null($prevId)) {
            $prev[] = $this::with(['category'=> function($query) {
                $query->field('id,name,ename');
            }])->field('id,title,category_id')->append(['url'])->find($prevId)->toArray();
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
        $this->setSuffix(self::getSuffixById($id));

        $next = [];

        $nextId = $this::with(['category' => function($query) {
            $query->field('id,name,ename');
        }])->where('id', '<=', $id - 1)
        ->where([
            ['category_id', '=', $cid],
            ['status', '=',1]
        ])
        ->order('id desc')
        ->value('id');

        if(!is_null($nextId)) {
            $next[] = $this::with(['category'=> function($query) {
                $query->field('id,name,ename');
            }])->field('id,title,category_id')->append(['url'])->find($nextId)->toArray();
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
                    $article = self::suffix(self::getSuffixById($id))
                    ->with(['category' => function($query) {
                        $query->field('id,name,ename');
                    }])
                    ->field('id,title,category_id,pv,create_time,description')
                    ->where('id', $id)
                    ->append(['url'])
                    ->find();

                    if(!is_null($article)) {
                        $article['hasImg']          = $article['has_image'] > 0 ? true : false;
                        $article['create_time']     = $article['create_time'];
                        $article['category_name']   = $article['category']['name'];
                        $article['description']     = $article['description'];
                        $article['link']            = $article['url'];
                        

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
                $data = User::field('avatar as avatar,name')
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

            $tags = $this->field('id,category_id,user_id,thumb,has_image,title,create_time,pv')
            ->whereIn('id', $arrId)
            ->where('status', '1')
            ->with([
                'user' => function($query){
                    $query->field('id,name,avatar');
                },'category' => function($query){
                    $query->field('id,name,ename');
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
                        'cate_name' => $v['category']['name'],
                        'pv'        => $v['pv'],
                        'time'      => date('Y-m-d', $v['create_time']),
                        'url'       => $v['url']
                    ];
                }
            }

            return $tagsArr;
        });

        return $allTags;
    }

    // 获取用户最新发帖列表
    public function getUserArtList(int $id) {
        $userArtList = Cache::remember('user_recently_post_'.$id, function() use($id) {
            return $this::field('id,category_id,title,flags,create_time,pv')
            ->with([
                'category' => function($query){
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

    /**
     * 获取用户发帖列表
     * @param array $data
     * @return array
     */
    public function getMyList(array $data): array
    {
        $query = $this->where(['user_id' => $data['uid']]);

        $count = $query->count();

        if($count === 0) {
            return ['count' => $count, 'data' => []];
        }
        
        $data =$query->with(['category' => function($query) {
            $query->field('id,ename,name');
        }])
        ->field('id,category_id,title,status,create_time,pv')
        ->page($data['page'])
        ->limit($data['limit'])
        ->order('id','desc')
        ->append(['url'])
        ->select()
        ->toArray();

        return ['count' => $count, 'data' => $data];
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
        return $this::field('id,user_id,category_id,title,content,forbid_comment,status,update_time,comments_num,flags')
        ->with([
            'user' => function($query){
                $query->field('id,name,avatar');
            },
            'category' => function($query){
                $query->field('id,ename,name');
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

        if(!empty($data['category_id'])){
            $where[] = ['category_id', '=', $data['category_id']];
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

        $data = $this::field('id,user_id,category_id,title,description,forbid_comment,status,update_time,comments_num')
        ->with([
             'user' => function($query){
                 $query->field('id,name,avatar');
             },
             'category' => function($query){
                 $query->field('id,ename,name');
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
                    $where[] = ['forbid_comment', '=', 1];
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

        if(!empty($data['category_id'])){
            $where[] = ['category_id', '=', $data['category_id']];
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
        // 总表数量
        $tableCount = count($tableSuffixArr);

        if($tableCount) {
            foreach($tableSuffixArr as $sfx) {
                $total = Article::suffix($sfx)->where($where)->count();
                $countArr[] = $total;
                $totals += $total;
            }
        }

        $map = [
            'countArr'      => $countArr,
            'totals'        => $totals,
            'tableSuffixArr'=> $tableSuffixArr,
            'tableCount'    => $tableCount
        ];
        // halt($map);
        // 总共页面数
        $lastPage = (int) ceil($map['totals'] / $limit); // 向上取整
        
        $datas = [];
        if($map['totals']) {

            if($page > $lastPage) {
                throw new BusinessException('no data');
            }
            
            // 最大偏移量
            $maxNum = $page * $limit;
            // 开始时的偏移量
            self::$offset = ($page - 1) * $limit;
            // newLimit首次=limit, newLimit 在数据介于两表之间时分量使用
            self::$newLimit = $limit;

            $field = 'id,category_id,user_id,title,forbid_comment,pv,status,create_time,update_time,comments_num,flags';

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
                            $query->field('id,name,nickname,avatar,vip');
                        },
                        'category' => function(Query $query){
                            $query->field('id,ename,name');
                        }
                    ])
                    // ->withCount(['comments'])
                    ->order('id', 'desc')
                    ->append(['url'])
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
                            $query->field('id,name,nickname,avatar,vip');
                        },
                        'category' => function(Query $query){
                            $query->field('id,ename,name');
                        }
                    ])
                    ->order('id', 'desc')
                    ->append(['url'])
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