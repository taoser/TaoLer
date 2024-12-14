<?php

namespace app\index\model;

use Exception;
use app\common\model\BaseModel;
use think\model\concern\SoftDelete;

class UserSignrule extends BaseModel
{
	protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    
	
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
	public function user()
	{
		//评论关联用户
		return $this->belongsTo('User','user_id','id');
	}
	
}