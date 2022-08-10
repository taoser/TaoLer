<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-08-10 14:36:20
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \github\TaoLer\app\common\model\Cate.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class Cate extends Model
{
	//软删除
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	
	// 查询类别信息
	public function getCateInfo(string $ename)
	{
		//
		return $this::field('ename,catename,detpl,desc')->where('ename',$ename)->cache('cate_'.$ename,600)->find();
	}

	// 删除类别
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