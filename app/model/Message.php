<?php

namespace app\model;

use Exception;
use think\model\concern\SoftDelete;

class Message extends BaseModel
{
	//软删除
	use SoftDelete;
    
    protected function getOptions(): array 
    {
        return [
            'autoWriteTimestamp'    => true,
            'deleteTime'            => 'delete_time',
            'defaultSoftDelete'     => null,
        ];
    }
	
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