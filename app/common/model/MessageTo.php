<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\Db;

class MessageTo extends Model
{
	use SoftDelete;	
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	//用户关联评论
	public function user()
	{
		return $this->hasMany('User','user_id','id');
	}
	
	public function messages()
	{
		//评论关联用户
		return $this->belongsTo('Message','message_id','id');
	}

	
}