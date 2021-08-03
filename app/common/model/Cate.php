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
	
	public function del($id)
	{
		$cates = $this->find($id);
		
		$res = $cates->delete();
		if($res){
			return 1;
		}else{
			return '删除分类失败';
		}
		
	}
	
	
}