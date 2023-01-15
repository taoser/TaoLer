<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Cache;
use think\facade\Config;

class Article extends Model
{
    protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
	//开启自动设置
	protected $auto = [];
	//仅新增有效
	protected $insert = ['create_time','status'=>1,'is_top'=>0,'is_hot'=>0];
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

    /**
     * 添加
     * @param array $data
     * @return int|string
     */
	public function add(array $data)
	{
		$superAdmin = User::where('id', $data['user_id'])->value('auth');
        // 超级管理员无需审核
		$data['status'] = $superAdmin ? 1 : Config::get('taoler.config.posts_check');
		$msg = $data['status'] ? '发布成功' : '发布成功，请等待审核';
		$result = $this->save($data);
		if($result == true) {
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
        $artTop = Cache::get('arttop');
        // 区分应用分类
        $appCateIdArr = Cate::where(['appname' => app('http')->getName()])->column('id');
        if (!$artTop) {
            $artTop = $this::field('id,title,title_color,cate_id,user_id,create_time,is_top,pv,jie,upzip,has_img,has_video,has_audio')->where([['is_top', '=', 1], ['status', '=', 1], ['cate_id', 'in', $appCateIdArr]])
            ->with([
                'cate' => function ($query) {
                    $query->where('delete_time', 0)->field('id,catename,ename');
                },
                'user' => function ($query) {
                    $query->field('id,name,nickname,user_img,area_id,vip');
                }
            ])->withCount(['comments'])
            ->order('create_time', 'desc')
            ->limit($num)
            ->append(['url'])
            ->select()
            ->toArray();
			
            Cache::tag('tagArtDetail')->set('arttop', $artTop, 60);
        }
        return $artTop;
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
        $artList = Cache::get('artlist');
        // 区分应用分类
        $appCateIdArr = Cate::where(['appname' => app('http')->getName()])->column('id');
		if(!$artList){
			$artList = $this::field('id,title,title_color,cate_id,user_id,create_time,is_hot,pv,jie,upzip,has_img,has_video,has_audio')
            ->with([
            'cate' => function($query){
                $query->where('delete_time',0)->field('id,catename,ename,detpl');
            },
			'user' => function($query){
                $query->field('id,name,nickname,user_img,area_id,vip');
			} ])
            ->withCount(['comments'])
            ->where([['status', '=', 1], ['is_top', '=', 0],['cate_id', 'in', $appCateIdArr]])
            ->order('create_time','desc')
            ->limit($num)
            ->append(['url'])
            ->select()
            ->toArray();

			Cache::tag('tagArt')->set('artlist',$artList,60);
		}
		return $artList;
    }

    /**
     * 热点文章
     * @param int $num 热点列表数量
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getArtHot(int $num)
    {
        $artHot = $this::field('id,cate_id,title,create_time')
        ->with(['cate' => function($query){
                $query->where('delete_time',0)->field('id,ename');
            }])
            ->withCount('comments')
            ->where(['status'=>1,'delete_time'=>0])
            ->whereTime('create_time', 'year')
            ->order('comments_count','desc')
            ->limit($num)
            ->withCache(120)
            ->append(['url'])
            ->select();

        return $artHot;
    }

    /**
     * 获取详情
     * @param int $id 文章id
     * @return array|mixed|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getArtDetail(int $id)
    {
        $article = Cache::get('article_'.$id);
        if(!$article){
            //查询文章
            $article = $this::field('id,title,content,status,cate_id,user_id,goods_detail_id,is_top,is_hot,is_reply,pv,jie,upzip,downloads,keywords,description,title_color,create_time,update_time')
            ->where(['status'=>1])
            ->with([
                'cate' => function($query){
                    $query->where('delete_time',0)->field('id,catename,ename');
                },
                'user' => function($query){
                    $query->field('id,name,nickname,user_img,area_id,vip,city')->withCount(['article','comments']);
                }
            ])
            ->withCount(['comments'])
            ->append(['url'])
            ->find($id);
            if (!is_null($article)) {
                Cache::tag('tagArtDetail')->set('article_'.$id, $article->toArray(), 3600);
            } else {
                return null;
            }
            
        }
        return $article;
    }

    /**
     * 获取分类列表
     * @param string $ename 分类英文名
     * @param string $type  all\top\hot\jie 分类类型
     * @param int $page 页面
     * @return mixed|\think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getCateList(string $ename, string $type, int $page = 1)
    {
        $where = [];
        // 区分应用分类
        $appCateIdArr = Cate::where(['appname' => app('http')->getName()])->column('id');
        $cateId = Cate::where('ename',$ename)->value('id');
        if($cateId){
            $where[] = ['cate_id' ,'=', $cateId];
        } else {
            if($ename != 'all'){
                // 抛出 HTTP 异常
                throw new \think\exception\HttpException(404, '异常消息');
            }
            $where[] = ['cate_id' ,'in',$appCateIdArr];
        }

        $artList = Cache::get('arts'.$ename.$type.$page);
        if(!$artList){
            switch ($type) {
                //查询文章,15个分1页
                case 'jie':
                    $where[] = ['jie','=', 1];
                    break;
                case 'hot':
                    $where[] = ['is_hot','=', 1];
                    break;
                case 'top':
                    $where[] = ['is_top' ,'=', 1];
                    break;
				case 'wait':
                    $where[] = ['jie','=', 0];
                    break;

            }
            $artList = $this::field('id,title,content,title_color,cate_id,user_id,create_time,is_top,is_hot,pv,jie,upzip,has_img,has_video,has_audio')
            ->with([
                'cate' => function($query) {
                    $query->where('delete_time',0)->field('id,catename,ename,appname');
                },
                'user' => function($query){
                    $query->field('id,name,nickname,user_img,area_id,vip');
                }
            ])->withCount(['comments'])
                ->where('status',1)
                ->where($where)
                ->limit(15)
                ->order(['create_time'=>'desc'])
                ->paginate([
                    'list_rows' => 15,
                    'page' => $page
                ])->append(['url'])->toArray();

            Cache::tag('tagArtDetail')->set('arts'.$ename.$type.$page,$artList,600);
        }
        return $artList;
    }

    // 获取用户发帖列表
    public function getUserArtList(int $id) {
        $userArtList = $this::field('id,cate_id,title,create_time,pv,is_hot')
        ->with(['cate' => function($query){
            $query->where(['delete_time'=>0,'status'=>1])->field('id,ename');
        }])
        ->where(['user_id' => $id,'status' => 1])
        ->order(['create_time'=>'desc'])
        ->append(['url'])
        ->cache(3600)
        ->select()
        ->toArray();

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
            ->order('create_time','desc')
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
     * @return void
     */
    public function getAllTags($tagId)
    {
        $allTags = $this::hasWhere('taglist',['tag_id'=>$tagId])
        ->with(['user' => function($query){
            $query->field('id,name,nickname,user_img,area_id,vip');
        },'cate' => function($query){
            $query->where('delete_time',0)->field('id,catename,ename');
        }])
        ->where(['status'=>1])
        ->order('pv desc')
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
        $allTags = $this::hasWhere('taglist',['tag_id'=>$tagId])
        ->with(['user' => function($query){
            $query->field('id,name,nickname,user_img,area_id,vip');
        },'cate' => function($query){
            $query->where('delete_time',0)->field('id,catename,ename');
        }])
        ->where(['status'=>1])
       // ->where('article.id', '<>', $id)
        ->order('pv desc')
        ->limit($limit)
        ->append(['url'])
        ->select()
        ->toArray();
    
        return $allTags;
    }

    /**
     * 上下文
     *
     * @param [type] $id 当前文章ID
     * @param [type] $cid 当前分类ID
     * @return void
     */
    public function getPrevNextArticle($id,$cid)
    {
        //上一篇
        $previous = $this::field('id,title,cate_id')
        ->where([
            ['id', '<', $id],
            ['cate_id', '=', $cid],
            ['status', '=',1]
        ])->order('id desc')->limit(1)->append(['url'])->select()->toArray();

        //下一篇
        $next = $this::field('id,title,cate_id')
        ->where([
            ['id', '>', $id],
            ['cate_id', '=', $cid],
            ['status', '=',1]
        ])->limit(1)->append(['url'])->select()->toArray();

        return ['previous' => $previous, 'next' => $next];
    }

    // 获取url
    public function getUrlAttr($value,$data)
    {
        if(config('taoler.url_rewrite.article_as') == '<ename>/') {
            $cate = Cate::field('id,ename')->find($data['cate_id']);
            return (string) url('article_detail',['id' => $data['id'],'ename' => $cate->ename]);
        } else {
            return (string) url('article_detail',['id' => $data['id']]);
        }
    }


}