<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\Db;
use think\facade\Session;

class Sign extends Model
{
    protected $pk = 'id'; //主键
    protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
   


    
	
	//用户关联评论
	public function user()
	{
		return $this->hasMany('User','user_id','id');
	}
	

	
}