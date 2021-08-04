<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Cache;
use think\facade\Config;

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
	
	//文章关联收藏
	public function collection()
	{
		return $this->hasMany(Collection::class);
	}
	
	//文章关联用户
	public function user()
	{
		return $this->belongsTo('User','user_id','id');
	}

    /**
     * 添加
     * @param array $data
     * @return int|string
     */
	public function add(array $data)
	{
		$data['status'] = Config::get('taoler.config.posts_check');
		$msg = $data['status'] ? '发布成功' : '发布成功，请等待审核';
		$result = $this->save($data);
		if($result) {
			return ['code'=>1,'msg'=>$msg];
		} else {
			return 'add_error';
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
		$article = $this->find($data['id']);
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
        if (!$artTop) {
            $artTop = $this::field('id,title,title_color,cate_id,user_id,create_time,is_top,pv,jie,upzip,has_img,has_video,has_audio')->where(['is_top' => 1, 'status' => 1])->with([
                'cate' => function ($query) {
                    $query->where('delete_time', 0)->field('id,catename,ename');
                },
                'user' => function ($query) {
                    $query->field('id,name,nickname,user_img,area_id,vip');
                }
            ])->withCount(['comments'])->order('create_time', 'desc')->limit($num)->select();
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
		if(!$artList){
			$artList = $this::field('id,title,title_color,cate_id,user_id,create_time,is_hot,pv,jie,upzip,has_img,has_video,has_audio')
            ->with([
            'cate' => function($query){
                $query->where('delete_time',0)->field('id,catename,ename,detpl');
            },
			'user' => function($query){
                $query->field('id,name,nickname,user_img,area_id,vip');
			} ])
            ->withCount(['comments'])->where(['status'=>1,'is_top'=>0])->order('create_time','desc')->limit($num)->select();
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
        $artHot = $this::field('id,title')
            ->withCount('comments')
            ->where(['status'=>1,'delete_time'=>0])
            ->whereTime('create_time', 'year')
            ->order('comments_count','desc')
            ->limit($num)
            ->withCache(60)->select();
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
            $article = $this::field('id,title,content,status,cate_id,user_id,is_top,is_hot,is_reply,pv,jie,upzip,downloads,tags,title_color,create_time')->where('status',1)->with([
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

    /**
     * 获取分类列表
     * @param string $ename 分类英文名
     * @param string $type  all\top\hot\jie 分类类型
     * @param int $page 页面
     * @param string $url
     * @param string $suffix
     * @return mixed|\think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getCateList(string $ename, string $type, int $page, string $url, string $suffix)
    {
        $where = [];
        $cateId = Cate::where('ename',$ename)->value('id');
        if($cateId){
            $where = ['cate_id' => $cateId];
        } else {
            if($ename != 'all'){
                // 抛出 HTTP 异常
                throw new \think\exception\HttpException(404, '异常消息');
            }
        }

        $artList = Cache::get('arts'.$ename.$type.$page);
        if(!$artList){
            switch ($type) {
                //查询文章,15个分1页
                case 'jie':
                    $artList = $this::field('id,title,content,title_color,cate_id,user_id,create_time,is_top,is_hot,pv,jie,upzip,has_img,has_video,has_audio')->with([
                        'cate' => function($query){
                            $query->where('delete_time',0)->field('id,catename,ename');
                        },
                        'user' => function($query){
                            $query->field('id,name,nickname,user_img,area_id,vip');
                        }
                    ])->withCount(['comments'])->where(['status'=>1,'jie'=>1])->where($where)->order(['is_top'=>'desc','create_time'=>'desc'])
                        ->paginate([
                            'list_rows' => 15,
                            'page' => $page,
                            'path' =>$url.'[PAGE]'.$suffix
                        ]);
                    break;

                case 'hot':
                    $artList = $this::field('id,title,content,title_color,cate_id,user_id,create_time,is_top,is_hot,pv,jie,upzip,has_img,has_video,has_audio')->with([
                        'cate' => function($query){
                            $query->where('delete_time',0)->field('id,catename,ename');
                        },
                        'user' => function($query){
                            $query->field('id,name,nickname,user_img,area_id,vip');
                        }
                    ])->withCount(['comments'])->where('status',1)->where($where)->where('is_hot',1)->order(['is_top'=>'desc','create_time'=>'desc'])
                        ->paginate([
                            'list_rows' => 15,
                            'page' => $page,
                            'path' =>$url.'[PAGE]'.$suffix
                        ]);
                    break;

                case 'top':
                    $artList = $this::field('id,title,content,title_color,cate_id,user_id,create_time,is_top,is_hot,pv,jie,upzip,has_img,has_video,has_audio')->with([
                        'cate' => function($query){
                            $query->where('delete_time',0)->field('id,catename,ename');
                        },
                        'user' => function($query){
                            $query->field('id,name,nickname,user_img,area_id,vip');
                        }
                    ])->withCount(['comments'])->where('status',1)->where($where)->where('is_top',1)->order(['is_top'=>'desc','create_time'=>'desc'])
                        ->paginate([
                            'list_rows' => 15,
                            'page' => $page,
                            'path' =>$url.'[PAGE]'.$suffix
                        ]);
                    break;
					
				case 'wait':
                    $artList = $this::field('id,title,content,title_color,cate_id,user_id,create_time,is_top,is_hot,pv,jie,upzip,has_img,has_video,has_audio')->with([
                        'cate' => function($query){
                            $query->where('delete_time',0)->field('id,catename,ename');
                        },
                        'user' => function($query){
                            $query->field('id,name,nickname,user_img,area_id,vip');
                        }
                    ])->withCount(['comments'])->where('status',1)->where($where)->where('jie',0)->order(['is_top'=>'desc','create_time'=>'desc'])
                        ->paginate([
                            'list_rows' => 15,
                            'page' => $page,
                            'path' =>$url.'[PAGE]'.$suffix
                        ]);
                    break;

                default:
                    $artList = $this::field('id,title,content,title_color,cate_id,user_id,create_time,is_top,is_hot,pv,jie,upzip,has_img,has_video,has_audio')->with([
                        'cate' => function($query){
                            $query->where('delete_time',0)->field('id,catename,ename');
                        },
                        'user' => function($query){
                            $query->field('id,name,nickname,user_img,area_id,vip');
                        }
                    ])->withCount(['comments'])->where('status',1)->where($where)->order(['is_top'=>'desc','create_time'=>'desc'])
                        ->paginate([
                            'list_rows' => 15,
                            'page' => $page,
                            'path' =>$url.'[PAGE]'.$suffix
                        ]);
                    break;
            }
            Cache::tag('tagArtDetail')->set('arts'.$ename.$type.$page,$artList,600);
        }
        return $artList;
    }


}