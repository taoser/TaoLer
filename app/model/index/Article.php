<?php
declare (strict_types = 1);

namespace app\model\index;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Cache;
use think\facade\Session;
use think\db\Query;

class Article extends Model
{
    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'title'         => 'string',
        'content'       => 'mediumtext',
        'status'        => 'enum',
        'cate_id'       => 'int',
        'user_id'       => 'int',
        'goods_detail_id' => 'int',
        'is_top'        => 'enum',
        'is_hot'        => 'enum',
        'is_reply'      => 'enum',
        'has_img'       => 'enum',
        'has_video'     => 'enum',
        'has_audio'     => 'enum',
        'pv'            => 'int',
        'jie'           => 'enum',
        'keywords'      => 'varchar',
        'description'   => 'text',
        'read_type'     => 'tinyint',
        'art_pass'      => 'varchar',
        'title_color'   => 'varchar',
        'title_font'    => 'varchar',
        'create_time'   => 'int',
        'update_time'   => 'int',
        'delete_time'   => 'int',
    ];

    protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
	//开启自动设置
	protected $auto = [];
	//仅新增有效
	protected $insert = ['create_time'];
	//仅更新有效
	protected $update = ['update_time'];
	
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;

    //文章关联用户
	public function user()
	{
		return $this->belongsTo(User::class);
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

    //文章关联Tag表
	public function taglist()
	{
		return $this->hasMany(Taglist::class);
	}

    // 模型事件，写入前
    public static function onBeforeWrite($user)
    {
    	// if ('thinkphp' == $user->name) {
        // 	return false;
        // }
    }

    // 模型事件，写入后
    public static function onAfterWrite($user)
    {
    	// if ('thinkphp' == $user->name) {
        // 	return false;
        // }
    }

    /**
     * 获取置顶文章
     * @param int $num 列表数量
     * @return mixed|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getTops(int $num = 5)
    {
        return Cache::remember('topArticle', function() use($num){
            $topIdArr = self::where(['is_top' => 1, 'status' => 1])->limit($num)->column('id');
            return self::field('id,title,title_color,cate_id,user_id,create_time,is_top,pv,has_img,has_video,has_audio,read_type')
                ->whereIn('id', $topIdArr)
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
                ->append(['url'])
                ->select();
        },600);
    }

    /**
     * 获取首页文章列表
     * @param int $num 列表显示数量
     * @return mixed|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getIndexs(int $num = 10)
    {
        $data =  Cache::remember('indexArticle', function() use($num){
            return self::field('id,title,title_color,cate_id,user_id,content,description,create_time,is_hot,pv,jie,has_img,has_video,has_audio,read_type,art_pass')
            ->with([
                'cate' => function(Query $query){
                    $query->field('id,catename,ename,detpl');
                },
                'user' => function(Query $query){
                    $query->field('id,name,nickname,user_img');
                }
            ])
            ->withCount(['comments'])
            ->where('status', 1)
            ->order('id','desc')
            ->limit($num)
            ->select()
            ->hidden(['art_pass'])
            ->toArray();

		}, 120);

        return $data;
    }

    /**
     * 热点文章
     * @param int $num 热点列表数量 评论或者pv排序
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getHots(int $num = 10)
    {
        return Cache::remember('article_hot', function() use($num){
            $comments = Comment::field('article_id, count(*) as count')
            ->hasWhere('article', ['status' => 1])
            ->group('article_id')
            ->limit($num)
            ->select();

            $idArr = [];
            foreach($comments as $v) {
                $idArr[] = $v->article_id;
            }
            $where = [];
            if(count($idArr)) {
                // 评论数
                $where = [
                    ['id', 'in', $idArr]
                ];

                $artHot = self::field('id,cate_id,title,create_time')
                ->withCount('comments')
                ->where($where)
                //->whereYear('create_time')
                // ->order('comments_count','desc')
                // ->append(['url'])
                ->select();

            } else {
                // pv数
                $where = [
                    ['status', '=', 1]
                ];

                $artHot = self::field('id,cate_id,title,create_time')
                ->withCount('comments')
                ->where($where)
                ->whereMonth('create_time')
                ->order('pv','desc')
                ->limit($num)
                // ->append(['url'])
                ->select();
            }
            return $artHot->toArray();
        }, 3600);
    }

    /**
     * 获取详情
     * @param int $id 文章id
     * @return mixed
     * @throws \Throwable
     */
    public function getDetail(int $id)
    {
        return Cache::remember('article_'.$id, function() use($id){
            //查询文章
            return $this::field('id,title,content,status,cate_id,user_id,goods_detail_id,is_top,is_hot,is_reply,pv,jie,keywords,description,read_type,art_pass,title_color,create_time,update_time')
            ->where(['status'=>1])
            ->with([
                'cate' => function(Query $query){
                    $query->field('id,catename,ename,detpl');
                },
                'user' => function(Query $query){
                    $query->field('id,name,nickname,user_img,area_id,vip,city')->withCount(['article','comments']);
                }
            ])
            ->withCount(['comments'])
            ->hidden(['art_pass'])
            ->append(['url'])
            ->find($id);
            
        }, 600);

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

    /**
     * 标签列表
     *
     * @param [type] $tagId 标签id
     * @param [type] $limit 输出数量
     * @return array
     */
    public function getAllTags($tagId)
    {
        $arrId = Taglist::where('tag_id', $tagId)->column('article_id');

        $allTags = $this::field('id,user_id,cate_id,title,create_time,is_hot')->where('id','in', $arrId)
        ->with(['user' => function($query){
            $query->field('id,name,nickname,user_img,area_id,vip');
        },'cate' => function($query){
            $query->field('id,catename,ename');
        }])
        ->where(['status'=>1])
        ->order('id desc')
        ->limit(20)
        ->append(['url'])
        ->select()
        ->toArray();
    
        return $allTags;
    }

    /**
     * 相关文章(标签)
     * 相同标签文章，不包含自己
     * @param [type] $tagId
     * @param [type] $limit
     * @return void
     */
    public function getRelationTags($tagId,$id,$limit)
    {
        $allTags = Cache::remember("relation_tag_post_{$tagId}_{$id}", function() use($tagId,$id,$limit) {

            $arrId = Taglist::where([
                ['tag_id', '=', $tagId],
                ['article_id','<>',$id]
            ])->column('article_id');

            $tags = $this::field('id,cate_id,user_id,title,content,create_time,pv,read_type,art_pass')->whereIn('id', $arrId)
            ->where(['status'=>1])
            ->with(['user' => function($query){
                $query->field('id,name,user_img');
                },'cate' => function($query){
                    $query->field('id,catename');
                }
            ])
            ->order('pv desc')
            ->limit($limit)
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
     * 上下文
     *
     * @param [type] $id 当前文章ID
     * @param [type] $cid 当前分类ID
     * @return void|array
     */
    public function getPrevNextArticle($id, $cid)
    {
        //上一篇
        $pIds = $this::where([
            ['id', '>=', $id], // >= <= 条件可以使用索引
            ['cate_id', '=', $cid],
            ['status', '=',1]
        ])->order('id asc')->limit(2)->column('id');

        if(count($pIds) === 2) {
            $previous = $this::field('id,title,cate_id')->append(['url'])->find($pIds[1])->toArray();
        } else {
            $previous = [];
        }
  
        //下一篇
        $nids = $this::where([
            ['id', '<=', $id],
            ['cate_id', '=', $cid],
            ['status', '=',1]
        ])->order('id desc')->limit(2)->column('id');

        if(count($nids) === 2) {
            $next = $this::field('id,title,cate_id')->append(['url'])->find($nids[1])->toArray();
        } else {
            $next = [];
        }

        return ['previous' => $previous, 'next' => $next];
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
                    $where[] = ['is_top', '=', '1'];
                    break;
                case '3':
                    $where[] = ['is_hot', '=', '1'];
                    break;
                case '4':
                    $where[] = ['is_reply', '=', '1'];
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

    // 两种模式 获取url
    public function getUrlAttr($value, $data)
    {

        if(config('taoler.url_rewrite.article_as') == '<ename>/') {
            $ename = Category::where('id', $data['cate_id'])->cache(true)->value('ename');
            return (string) url('detail',['id' => $data['id'], 'ename' => $ename]);
        }
        return (string) url('detail', ['id' => $data['id']]);
    }

    // 内容是否加密
    public function getContentAttr($value, $data)
    {
        //解密
        if($data['read_type'] == 1 && (session('art_pass_'.$data['id']) !== $data['art_pass'])) {
            return '内容已加密！请输入正确密码查看！';
        }
        return $value;
    }


}