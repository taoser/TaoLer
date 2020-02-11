<?php
namespace app\common\model;

use think\Model;

class UserArea extends Model
{
	protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    
	
	//软删除
	//use SoftDelete;
	//protected $deleteTime = 'delete_time';
	//protected $defaultSoftDelete = 0;
	
	public function user()
	{
		//评论关联用户
		return $this->belongsTo('User','user_id','id');
	}
	
}