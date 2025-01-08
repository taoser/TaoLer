<?php
declare (strict_types = 1);

namespace app\index\model;


use Exception;
use app\common\model\BaseModel;
use think\model\concern\SoftDelete;
use think\db\Query;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Session;
use app\observer\ArticleObserver;
use app\common\lib\IdEncode;
use think\facade\Route;

class Article extends BaseModel
{
    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'title'         => 'string',
        'content'       => 'mediumtext',
        'keywords'      => 'varchar',
        'description'   => 'text',
        'cate_id'       => 'int',
        'user_id'       => 'int',
        'pv'            => 'int',
        'jie'           => 'enum',
        'is_top'        => 'enum',
        'is_hot'        => 'enum',
        'is_reply'      => 'enum',
        'has_img'       => 'enum',
        'has_video'     => 'enum',
        'has_audio'     => 'enum',
        'read_type'     => 'enum',
        'status'        => 'enum',
        'title_color'   => 'varchar',
        'art_pass'      => 'varchar',
        'goods_detail_id' => 'int',
        'media'         => 'json',
        'create_time'   => 'int',
        'update_time'   => 'int',
        'delete_time'   => 'int',
    ];

    // 模型事件
    protected $eventObserver = ArticleObserver::class;

    protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
	//开启自动设置
	protected $auto = [];
	//仅新增有效
	protected $insert = ['create_time', 'status' => '1', 'is_top' => '0', 'is_hot' => '0'];
	//仅更新有效
	protected $update = ['update_time'];
	
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;

    // 设置json类型字段
	protected $json = ['media'];

    // 延迟写入pv
    protected $lazyFields = ['pv'];

    // 新的数量, 数据介于两表之间分量时使用
    protected static $newLimit;
    // 当前分页数据偏移量
    protected static $offset;
    // 当前用到的数据总和
    protected static $currentTotalNum = 0;

    /**
     * 获取所有分表的后缀数组
     * ['_1','_2','_3']
     * @return array
     */
    public static function getTablesSfx1(): array
    {
        $suffixArr = [];
        $tableArr = self::getTables();
        if(count($tableArr)) {
            $suffixArr = array_map(function ($element) {
                $lastUnderscorePos = strrpos($element, '_');
                if ($lastUnderscorePos!== false) {
                    return substr($element, $lastUnderscorePos);
                }
                return $element;
            }, $tableArr);
        }

        return array_reverse($suffixArr);
    }

    /**
     * 获取子表数组
     * ['tao_article_1','tao_article_2','tao_article_3']
     * @return array
     */
    public static function getTables1(): array
    {
        $tables = [];
        // 表前缀
        $prefix = config('database.connections.mysql.prefix') . 'article';
        $sql = "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME REGEXP '{$prefix}_[0-9]+';";
        $sqlTables = Db::query($sql);
        // 是否有子数据表
        if(count($sqlTables)){
            $tables = array_column($sqlTables,'TABLE_NAME');
        }

        return $tables;
    }

    /**
     * 自动获取分表后缀 新增时不用传参，自动判定maxID，查询 编辑 删除时必须传id值
     * '_1','_2'
     * @param integer|null $id
     * @return string
     */
    public static function getSfx1(?int $id = null): string
    {
        $suffix = '';
        // 增加数据时，判定是否需要新建数据库
        if($id === null) {
            // 表前缀
            $prefix = config('database.connections.mysql.prefix') . 'article';
            $sql = "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME REGEXP '{$prefix}_[0-9]+';";
		    $tables = Db::query($sql);
			// 是否有子数据表
            if(count($tables)){
                $arr = array_column($tables,'TABLE_NAME');
				$lastTableName = end($arr);
				// 数据库最大id
                $maxId = (int) Db::table($lastTableName)->max('id');
				if($maxId === 0) {
					// 数据库为空
					$nameArr = explode("_", $lastTableName);
					// 当前空表序号
					$num =  (int) end($nameArr);
					// 空表前最大ID
					$maxId = 100 * $num;
				}
            } else {
				// 仅有一张article表
                $maxId = (int) DB::name('article')->max('id');
            }

			// 表后缀数字（层级）
            $num = (int) floor($maxId / 100);
            
        } else {
			// 查、改、删
            $num = (int) floor(($id - 1) / 100);
        }

        if($num > 0) {
            // 数据表后缀
            $suffix = "_{$num}";
        }

        return $suffix;
    }


	
    //文章关联栏目表
    public function cate()
    {
        return $this->belongsTo(Category::class);
    }
	
	//文章关联评论
	public function comments()
	{
		return $this->hasMany(Comment::class);
	}
	
	//文章关联收藏
	public function collection()
	{
		return $this->hasMany(Collection::class);
	}

    //文章关联用户点赞
	public function userzan()
	{
		return $this->hasMany(UserZan::class);
	}
	
	//文章关联用户
	public function user()
	{
		return $this->belongsTo(User::class);
	}

    //文章关联Tag表
	public function taglist()
	{
		return $this->hasMany(Taglist::class);
	}


    /**
     * 添加
     * @param array $data
     * @return int|string|array
     */
	public function add(array $data)
	{
        $result = $this->save($data);
        
        if(!$result) {
            throw new Exception('save error');
        }

        return ['id'=> $this->id];
	}

    /**
     * 编辑
     *
     * @param array $data
     * @return void
     */
	public function edit(array $data)
	{
		$result = $this->save($data);

		if(!$result) {
			throw new Exception('edit error');
		}

        return true;
	}

    /**
     * 多选和单选删除
     *
     * @param array $ids
     * @return void
     */
    public function remove(array $ids)
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
     * 获取置顶文章
     * @param int $num 列表数量
     * @return mixed|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTops(int $num = 5)
    {
        return Cache::remember('top_article', function() use($num){
            $tops = Article::field('id')->where('is_top', '1')->where('status', '1')->limit($num)->select();
            $ids = $tops->toArray();
            $idArr = array_column($ids, 'id');
            if(empty($idArr)) {
                return [];
            }

            return $this::field('id,title,title_color,cate_id,user_id,create_time,is_top,pv,has_img,has_video,has_audio,read_type,media')
                ->whereIn('id', $idArr)
                ->with([
                    'cate' => function (Query $query) {
                        $query->field('id,catename,ename');
                    },
                    'user' => function (Query $query) {
                        $query->field('id,name,nickname,user_img');
                    }
                ])
                ->withCount(['comments'])
                ->order('id', 'desc')
                ->append(['url','master_pic'])
                ->select()
                ->toArray();
        }, 600);
    }

    /**
     * 获取首页文章列表
     * @param int $num 列表显示数量
     * @return mixed|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getIndexs(int $num = 10)
    {
        $indexs = Cache::remember('idx_article', function() use($num){
            
            $map = self::getSuffixMap(['status' => 1], Article::class);

            $field = 'id,title,title_color,cate_id,user_id,content,description,is_hot,pv,jie,has_img,has_video,has_audio,read_type,art_pass,create_time,media';
            // 判断是否有多个表
            if($map['suffixCount'] > 1) {

                // 分表数量够
                if($map['counts'][0] >= $num) {
                    $data = $this::suffix($map['suffixArr'][0])->field($field)
                        ->with([
                        'cate' => function(Query $query){
                            $query->field('id,catename,ename,detpl');
                        },
                        'user' => function(Query $query){
                            $query->field('id,name,nickname,user_img');
                        } ])
                        ->withCount(['comments'])
                        ->where('status', '1')
                        ->order('id','desc')
                        ->hidden(['art_pass'])
                        ->append(['enid'])
                        ->append(['url','master_pic'])
                        ->limit($num)
                        ->select()
                        ->toArray();

                } else {
                    // 第一个分表 数量不够 取第二个分表数
                    $data = $this::suffix($map['suffixArr'][0])->field($field)
                        ->with([
                        'cate' => function(Query $query){
                            $query->field('id,catename,ename,detpl');
                        },
                        'user' => function(Query $query){
                            $query->field('id,name,nickname,user_img');
                        } ])
                        ->withCount(['comments'])
                        ->where('status', '1')
                        ->order('id','desc')
                        ->hidden(['art_pass'])
                        ->append(['enid'])
                        ->append(['url','master_pic'])
                        ->limit($map['counts'][0])
                        ->select()
                        ->toArray();

                    $data1 = $this::suffix($map['suffixArr'][0])->field($field)
                        ->with([
                        'cate' => function(Query $query){
                            $query->field('id,catename,ename,detpl');
                        },
                        'user' => function(Query $query){
                            $query->field('id,name,nickname,user_img');
                        } ])
                        ->withCount(['comments'])
                        ->where('status', '1')
                        ->order('id','desc')
                        ->hidden(['art_pass'])
                        ->append(['enid'])
                        ->append(['url','master_pic'])
                        ->limit($num - $map['counts'][0])
                        ->select()
                        ->toArray();

                    $data = array_merge($data, $data1);
                }
            } else {
                // 单表
                $data = $this::field($field)
                    ->with([
                    'cate' => function(Query $query){
                        $query->field('id,catename,ename,detpl');
                    },
                    'user' => function(Query $query){
                        $query->field('id,name,nickname,user_img');
                    } ])
                    ->withCount(['comments'])
                    ->where('status', '1')
                    ->order('id','desc')
                    ->hidden(['art_pass'])
                    ->append(['enid'])
                    ->append(['url','master_pic'])
                    ->limit($num)
                    ->select()
                    ->toArray();
            }

            // return $data->hidden(['art_pass'])->toArray();

            // if(config('taoler.id_status') === 1) {
            //     $sqids = new Sqids(config('taoler.id_alphabet'), config('taoler.id_minlength'));
            //     foreach($data as $k => $v) {
            //         $data[$k]['id'] = $sqids->encode([$v['id']]);
            //     }
            // }

            return $data;

		}, 120);

        return $indexs;
    }

    /**
     * 热点文章
     * @param int $num 热点列表数量 评论或者pv排序
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getHots(int $num = 10)
    {
        $hots =  Cache::remember('hots', function() use($num){

            $comment = Db::name('comment')
            ->alias('c')
            ->field('c.article_id, count(*) as count')
            ->join('article a', 'c.article_id = a.id')
            ->whereMonth('c.create_time')
            ->where(['c.status' => 1, 'c.delete_time' => 0])
            ->where(['a.status' => 1, 'a.delete_time' => 0])
            ->group('c.article_id')
            ->order('count', 'desc')
            ->limit($num)
            ->select()
            ->toArray();

            $idArr = array_column($comment, 'article_id');
           
            if(count($idArr)) {
                // 评论数
                $artHot = $this::field('id,cate_id,title,create_time')
                ->whereIn('id', $idArr)
                ->withCount('comments')
                ->order('comments_count','desc')
                ->append(['url'])
                ->select();

            } else {
                // pv数
                $artHot = $this::field('id,cate_id,title,create_time')
                ->withCount('comments')
                ->whereMonth('create_time')
                ->where('status', '1')
                ->order('pv','desc')
                ->limit($num)
                ->append(['url'])
                ->select();
            }

            return $artHot;
        }, 3600);

        return $hots;
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
            return $this::field('id,title,content,status,cate_id,user_id,goods_detail_id,is_top,is_hot,is_reply,pv,jie,keywords,description,read_type,art_pass,title_color,create_time,update_time')
            ->where('id', $id)
            ->where('status', '1')
            ->with([
                'cate' => function(Query $query){
                    $query->field('id,catename,ename,detpl');
                },
                'user' => function(Query $query){
                    $query->field('id,name,nickname,user_img,area_id,vip,city');
                }
            ])
            ->hidden(['art_pass'])
            ->append(['url'])
            ->findOrFail();
            
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
            $next[] = $this::field('id,title,cate_id')->append(['url'])->find($nextId)->toArray();
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
        return Cache::remember('rela_'.$id, function() use($id,$limit){
            $tagId = Taglist::where('article_id', $id)->value('tag_id');
            $articleIdArr = Taglist::where('tag_id', $tagId)
            ->where('article_id','<>', $id)
            ->limit($limit)
            ->column('article_id');

            $data = [];
            if(count($articleIdArr)) {
                foreach($articleIdArr as $id) {
                    $article = self::suffix(self::byIdGetSuffix($id))
                    ->field('id,title,cate_id')
                    ->where('id', $id)
                    ->append(['url'])
                    ->find();

                    if(!is_null($article)) {
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

            $tags = $this::field('id,cate_id,user_id,title,create_time,pv,read_type,art_pass')
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
                        'id' => $v['id'],
                        'hasImg' => getOnepic($v['content']) ? true : false,
                        'img' => getOnepic($v['content']) ? (strstr(getOnepic($v['content']), 'http') ? getOnepic($v['content']) : config('base.domain').getOnepic($v['content'])) : '',
                        'title' => $v['title'],
                        'desc' => getArtContent($v['content']),
                        'auther' => $v['user']['name'],
                        'cate_name' => $v['cate']['catename'],
                        'pv'    => $v['pv'],
                        'time' => date('Y-m-d',strtotime($v['create_time'])),
                        'url' => $v['url']
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

    // 获取用户发帖列表
    public function getUserArtList(int $id) {
        $userArtList = Cache::remember('user_recently_post_'.$id, function() use($id) {
            return $this::field('id,cate_id,title,create_time,pv,is_hot')
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
        $map = []; //所有的查询条件封装到数组中
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
        return $this::field('id,user_id,cate_id,title,content,is_top,is_hot,is_reply,status,update_time,read_type,art_pass')
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

        $data = $this::field('id,user_id,cate_id,title,description,is_top,is_hot,is_reply,status,update_time,read_type,art_pass')
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
        // dump($page);
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

        $map = [
            'counts'    => $counts,
            'totals'    => $totals,
            'suffixArr' => $suffixArr,
            'suffixCount' => $suffixCount
        ];

        // 总共页面数
        $lastPage = (int) ceil($map['totals'] / $limit); // 向上取整

        if($map['totals']) {

            if($page > $lastPage) {
                throw new Exception('no data');
            }

            $datas = [];
            // 最大偏移量
            $maxNum = $page * $limit;
            // 开始时的偏移量
            self::$offset = ($page - 1) * $limit;
            // newLimit首次=limit, newLimit 在数据介于两表之间时分量使用
            self::$newLimit = $limit;

            $field = 'id,cate_id,user_id,title,content,description,title_color,create_time,is_top,is_hot,is_reply,pv,jie,has_img,has_video,has_audio,read_type,status,update_time';

            for($i = 0; $i < $map['suffixCount']; $i++) {

                self::$currentTotalNum += $map['counts'][$i];

                // 1.可以完全取到 在第一组分表中就可以完全查询到
                if((self::$currentTotalNum - $maxNum) >= 0){
                    // echo 123;
                
                    $articles = Article::suffix($map['suffixArr'][$i])
                    ->field('id')
                    ->where($where)
                    ->order('id', 'desc')
                    ->limit(self::$offset, self::$newLimit)
                    ->select();

                    $ids = $articles->toArray();
                    $idArr = array_column($ids, 'id');

                    $list =  Article::suffix($map['suffixArr'][$i])
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
                    ->withCount(['comments'])
                    ->order('id', 'desc')
                    // ->hidden(['art_pass'])
                    ->append(['aurl'])
                    ->select()
                    ->toArray();
                    
                    $datas = array_merge($datas, $list);
                    break;
                } 

                // 2.数据介于2表之间 第一组和第二组各取部分数据
                if((self::$currentTotalNum - $maxNum) < 0 && ($maxNum - self::$currentTotalNum - $limit) < 0 ) {
                    // echo 234;

                    $articles = Article::suffix($map['suffixArr'][$i])
                    ->field('id')
                    ->where($where)
                    ->order('id', 'desc')
                    ->limit(self::$offset, self::$newLimit)
                    ->select();
                    $ids = $articles->toArray();
                    $idArr = array_column($ids, 'id');

                    $list =  Article::suffix($map['suffixArr'][$i])
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
                    ->withCount(['comments'])
                    ->order('id', 'desc')
                    ->hidden(['art_pass'])
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
            'total' => $map['totals'],
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => $lastPage,
            'data' => $datas
        ];

    }

    // 两种模式 获取url
    public function getUrlAttr($value, $data)
    {
        $data['id'] = IdEncode::encode($data['id']);
        
        if(config('taoler.url_rewrite.article_as') == '<ename>/') {
            $ename = Category::where('id', $data['cate_id'])->cache(true)->value('ename');
            return (string) url('article_detail', ['id' => $data['id'],'ename' => $ename])->domain(true);
        }

        return (string) url('article_detail',['id' => $data['id']])->domain(true);
    }

    /**
     * 获取主图
     *
     * @param [type] $value
     * @param [type] $data
     * @return void
     */
    public function getMasterPicAttr($value, $data)
    {
        if(isset($data['media']->image)) {
            return $data['media']->image[0];
        }
    }

    /**
     * APP应用转换,在后台admin应用转换为在其它app应用的路径
     * /admin/user/info转换为 /index/user/info
     * $appName 要转换为哪个应用
     * $url 路由地址
     * return string
     */
    public function getAurlAttr($value, $data)
    {
        $detail_as = config('taoler.url_rewrite.article_as') ?: 'detail/'; //详情页URL别称

        $data['id'] = IdEncode::encode($data['id']);
        
        if(config('taoler.url_rewrite.article_as') == '<ename>/') {
            $ename = Category::where('id', $data['cate_id'])->cache(true)->value('ename');
            $url = (string) Route::buildUrl("{$ename}/{$data['id']}");
        } else if(!empty(config('taoler.url_rewrite.article_as'))) {
            $url = (string) Route::buildUrl(config('taoler.url_rewrite.article_as')."{$data['id']}");
        } else {
            $url = (string) Route::buildUrl("{$data['id']}");
        }

        $appName = 'index';

        // 判断应用是否绑定域名
        $app_bind = array_search($appName, config('app.domain_bind'));
        // 判断应用是否域名映射
        $app_map = array_search($appName, config('app.app_map'));

        // 判断admin应用是否绑定域名
        $bind_admin = array_search('admin',config('app.domain_bind'));
        // 判断admin应用是否域名映射
        $map_admin = array_search('admin',config('app.app_map'));

        //1.admin绑定了域名
        if($bind_admin) {
            // 1.应用绑定了域名
            if($app_bind) {
                return $url;
            }
            // 2.应用进行了映射
            if($app_map){
                return $appName . $url;
            }
            // 3.应用未绑定域名也未进行映射
            return $appName . $url;
        }

        //2.admin进行了映射
        if($map_admin) {
            // 1.应用绑定了域名
            if($app_bind) {
                return str_replace($map_admin, '', $url);;
            }
            // 2.应用进行了映射
            if($app_map){
                return str_replace($map_admin, $app_map, $url);
            }
            // 3.应用未绑定域名也未进行映射
            return str_replace($map_admin, $appName, $url);
        }
        //3.admin未绑定域名也未映射
        // 1.应用绑定了域名
        if($app_bind) {
            return $url;
        }
        // 2.应用进行了映射
        if($app_map){
            return str_replace('admin', $app_map, $url);
        }
        return str_replace('admin', $appName, $url);
        
    }

    // 内容是否加密
    public function getContentAttr($value, $data)
    {
        //解密
        if($data['read_type'] == 1 && isset($data['art_pass']) && (session('art_pass_'.$data['id']) !== $data['art_pass'])) {
            return 'Encrypted! Please enter the correct password to view!';
        }
        return $value;
    }

}