<?php
namespace app\model\index;

use think\Model;
use think\model\concern\SoftDelete;

class Message extends Model
{
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
	//用户关联评论
	public function user()
	{
		return $this->hasMany('User','user_id','id');
	}
	
	//发件箱关联收件箱
	public function messageto()
	{
		return $this->hasMany('MessageTo','message_id','id');
	}
	
}