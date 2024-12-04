<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Cache;
use think\facade\Session;
use think\facade\Config;
use think\db\Query;
use think\facade\Db;
use Exception;


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
	protected $insert = ['create_time', 'status'=>1, 'is_top'=>'0', 'is_hot'=>'0'];
	//仅更新有效
	protected $update = ['update_time'];
	
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
    //文章关联栏目表
    public function cate()
    {
        return $this->belongsTo(Cate::class);
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
		return $this->belongsTo('User','user_id','id');
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
     * 添加
     * @param array $data
     * @return int|string|array
     */
	public function add(array $data)
	{
		$superAdmin = User::where('id', $data['user_id'])->value('auth');
        // 超级管理员无需审核
		$data['status'] = $superAdmin ? 1 : Config::get('taoler.config.posts_check');
		$msg = $data['status'] ? '发布成功' : '发布成功，请等待审核';
		$result = $this->save($data);
		if($result) {
			return ['code' => 1, 'msg' => $msg, 'data' => ['status' => $data['status'], 'id'=> $this->id]];
		} else {
			return ['code' => -1, 'msg'=> '添加文章失败'];
		}
	}

    /**
     * 编辑
     * @param array $data
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public function edit(array $data)
	{
		$article = $this::find($data['id']);
		$result = $article->save($data);
		if($result) {
			return 1;
		} else {
			return 'edit_error';
		}
	}

    /**
     * 获取置顶文章
     * @param int $num 列表数量
     * @return mixed|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getArtTop(int $num)
    {
        return Cache::remember('topArticle', function() use($num){
            $topIdArr = $this::where(['is_top' => 1, 'status' => 1])->limit($num)->column('id');
             return $this::field('id,title,title_color,cate_id,user_id,create_time,is_top,pv,has_img,has_video,has_audio,read_type')
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
                ->select()
                ->toArray();
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
    public function getArtList(int $num)
    {
        return Cache::remember('indexArticle', function() use($num){
            $data = $this::field('id,title,title_color,cate_id,user_id,content,description,create_time,is_hot,pv,jie,has_img,has_video,has_audio,read_type,art_pass')
            ->with([
            'cate' => function(Query $query){
                $query->field('id,catename,ename,detpl');
            },
			'user' => function(Query $query){
                $query->field('id,name,nickname,user_img');
			} ])
            ->withCount(['comments'])
            ->where('status', 1)
            ->order('id','desc')
            ->limit($num)
            ->append(['url'])
            ->select();

            return $data->hidden(['art_pass'])->toArray();
		},120);

    }

    /**
     * 热点文章
     * @param int $num 热点列表数量 评论或者pv排序
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getArtHot(int $num = 10)
    {
        $hots =  Cache::remember('article_hot', function() use($num){

            $comment = Db::name('comment')
            ->alias('c')
            ->field('c.article_id, count(*) as count')
            ->whereMonth('c.create_time')
            ->join('article a', 'c.article_id = a.id')
            ->where(['c.status' => 1, 'c.delete_time' => 0])
            ->where(['a.status' => 1, 'a.delete_time' => 0])
            ->group('c.article_id')
            ->order('count', 'desc')
            ->limit($num)
            ->select()
            ->toArray();

            $idArr = array_column($comment, 'article_id');
            
            $where = [];
            if(count($idArr)) {
                // 评论数
                $where = [
                    ['id', 'in', $idArr]
                ];

                $artHot = $this::field('id,cate_id,title,create_time')
                ->withCount('comments')
                ->where($where)
                //->whereYear('create_time')
                ->order('comments_count','desc')
                ->append(['url'])
                ->select();

            } else {
                // pv数
                $where = [
                    ['status', '=', '1']
                ];

                $artHot = $this::field('id,cate_id,title,create_time')
                ->withCount('comments')
                ->where($where)
                ->whereMonth('create_time')
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
    public function getArtDetail(int $id)
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
                    $query->field('id,name,nickname,user_img,area_id,vip,city');
                }
            ])
            ->hidden(['art_pass'])
            ->append(['url'])
            ->find($id);
            
        }, 600);

    }

    /**
     * 分类数据
     * @param string $ename
     * @param string $type  all\top\hot\jie 分类类型
     * @param int $page
     * @return mixed
     * @throws \Throwable
     */
    public function getCateList(string $ename, int $page = 1, string $type = 'all', int $limit = 15)
    {
        $where = [];
        $cateId = Cate::where('status', 1)->where('ename', $ename)->value('id');

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
    public function getUrlAttr($value,$data)
    {
        if(config('taoler.url_rewrite.article_as') == '<ename>/') {
            $ename = Cate::where('id', $data['cate_id'])->cache(true)->value('ename');
            return (string) url('article_detail',['id' => $data['id'],'ename' => $ename]);
        }
        return (string) url('article_detail',['id' => $data['id']]);
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