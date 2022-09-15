<?php
namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class App extends Model
{
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
	//
	public function plugins()
	{
		return $this->hasMany(Plugins::class);
	}
	
	
}