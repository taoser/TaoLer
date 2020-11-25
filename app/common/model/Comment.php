<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Comment extends Model
{
	protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
	
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
	public function article()
	{
		//评论关联文章
		return $this->belongsTo('Article','article_id','id');
	}
	
	public function user()
	{
		//评论关联用户
		return $this->belongsTo('User','user_id','id');
	}

	//获取评论
    public function getComment($id)
    {
	    $comments = $this::where(['article_id'=>$id,'status'=>1])->order(['cai'=>'asc','create_time'=>'asc'])->paginate(10);
	    return $comments;
    }
	
}