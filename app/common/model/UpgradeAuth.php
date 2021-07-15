<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class UpgradeAuth extends Model
{
	//protected $pk = 'id'; //主键
    protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    
	
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
   public function getAuthLevelAttr($value)
    {
        $level = [0=>'免费版',1=>'初级版',2=>'高级版'];
        return $level[$value];
    }

}