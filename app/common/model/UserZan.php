<?php
namespace app\common\model;

use think\Model;
//use think\model\concern\SoftDelete;

class UserZan extends Model
{
	protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    
	
	//软删除
	//use SoftDelete;
	//protected $deleteTime = 'delete_time';
	//protected $defaultSoftDelete = 0;
	
	public function comment()
	{
		//评论关联文章
		return $this->belongsTo('Comment','comment_id','id');
	}
	
	public function user()
	{
		//评论关联用户
		return $this->belongsTo('User','user_id','id');
	}
	
}