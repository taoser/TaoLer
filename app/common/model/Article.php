<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Cache;

class Article extends Model
{
	//protected $pk = 'id'; //主键
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
        return $this->belongsTo('Cate','cate_id','id');
    }
	
	//文章关联评论
	public function comments()
	{
		return $this->hasMany(Comment::class);
	}
	
	//文章关联用户
	public function user()
	{
		return $this->belongsTo('User','user_id','id');
	}
	
	//文章添加
	public function add($data)
	{
		$result = $this->save($data);

		if($result) {
			return 1;
		} else {
			return 'add_error';
		}
	}
	
	//文章编辑
	public function edit($data)
	{
		$article = $this->find($data['id']);
		$result = $article->save($data);
		if($result) {
			return 1;
		} else {
			return 'edit_error';
		}
	}
	
	//文章
	public function detail()
	{
		$arts = Article::all();
		return $arts;
	}

    /**
     * 获取置顶文章
     * @return mixed|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getArtTop($pnum)
    {
        $artTop = Cache::get('arttop');
        if (!$artTop) {
            $artTop = $this::field('id,title,title_color,cate_id,user_id,create_time,is_top,jie,pv')->where(['is_top' => 1, 'status' => 1, 'delete_time' => 0])->with([
                'cate' => function ($query) {
                    $query->where('delete_time', 0)->field('id,catename,ename');
                },
                'user' => function ($query) {
                    $query->field('id,name,nickname,user_img,area_id,vip');
                }
            ])->withCount(['comments'])->order('create_time', 'desc')->limit($pnum)->select();
            Cache::tag('tagArtDetail')->set('arttop', $artTop, 60);
        }
        return $artTop;
    }

    /**
     * 获取首页文章列表，显示20个。
     * @return mixed|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getArtList($pnum)
    {
        $artList = Cache::get('artlist');
		if(!$artList){
			$artList = $this::field('id,title,title_color,cate_id,user_id,create_time,is_hot,jie,pv')
            ->with([
            'cate' => function($query){
                $query->where('delete_time',0)->field('id,catename,ename');
            },
			'user' => function($query){
                $query->field('id,name,nickname,user_img,area_id,vip');
			} ])
            ->withCount(['comments'])->where(['status'=>1,'delete_time'=>0])->order('create_time','desc')->limit($pnum)->select();
			Cache::tag('tagArt')->set('artlist',$artList,60);
		}
		return $artList;
    }

    //热议文章
    public function getArtHot($pnum)
    {
        $artHot = $this::field('id,title')
            ->withCount('comments')
            ->where(['status'=>1,'delete_time'=>0])
            ->whereTime('create_time', 'year')
            ->order('comments_count','desc')
            ->limit($pnum)
            ->withCache(60)->select();
        return $artHot;
    }

    //详情
    public function getArtDetail($id)
    {
        $article = Cache::get('article_'.$id);
        if(!$article){
            //查询文章
            $article = $this::field('id,title,content,status,cate_id,user_id,is_top,is_hot,is_reply,pv,jie,upzip,tags,title_color,create_time')->where('status',1)->with([
                'cate' => function($query){
                    $query->where('delete_time',0)->field('id,catename,ename');
                },
                'user' => function($query){
                    $query->field('id,name,nickname,user_img,area_id,vip');
                }
            ])->find($id);
            Cache::tag('tagArtDetail')->set('article_'.$id,$article,3600);
        }
        return $article;
    }
}