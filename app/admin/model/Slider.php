<?php
namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class Slider extends Model
{
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
	public function getSlidTypeAttr($value)
	{
		$slid_type = [1=>'首页幻灯',2=>'通用右底',3=>'首页赞助',4=>'文章赞助',5=>'分类赞助',6=>'友情链接'];
        return $slid_type[$value];
	}
	
	
}