<?php
namespace app\model\index;

use think\Model;
use think\model\concern\SoftDelete;

class Collection extends Model
{
	//protected $pk = 'id'; //主键
    protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    
	
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
    //收藏关联文章
	public function article()
	{
		return $this->belongsTo(Article::class);
	}

}