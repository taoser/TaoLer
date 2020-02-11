<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Cate extends Model
{
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
	public function del($data)
	{
		$cates = $this->find($data['id']);
		
		$res = $cates->delete();
		if($res){
			return 1;
		}else{
			return -1;
		}
		
	}
	
	
}