<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

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
		return $this->hasMany('Comment','article_id','id');
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
			return '文章添加失败！';
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
			return '文章修改失败！';
		}
	}
	
	//文章
	public function detail()
	{
		$arts = Article::all();
		return $arts;
	}
	
	
	//置顶文章
	public function artTop()
	{
		$artTop = Article::where('status',1)->where('is_top',1)->select();
		
		return $artTop;
	}
}