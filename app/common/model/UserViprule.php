<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class UserViprule extends Model
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
	
	//获取等级名
	public function getVipAttr($value)
	{
		$vip = [0=>'普通',1=>'L1',2=>'L2',3=>'L3',4=>'L4',5=>'L5'];
        return $vip[$value];
	}
	
}