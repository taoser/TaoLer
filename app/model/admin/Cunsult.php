<?php
namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class Cunsult extends Model
{
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
		
	
}