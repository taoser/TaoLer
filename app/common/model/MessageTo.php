<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-27 21:25:14
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \github\TaoLer\app\common\model\MessageTo.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\Db;

class MessageTo extends Model
{
	use SoftDelete;	
	protected $deleteTime = 'delete_time';
	protected $defaultSoftDelete = 0;
	//用户关联评论
	public function user()
	{
		return $this->hasMany('User','user_id','id');
	}
	
	public function messages()
	{
		//评论关联用户
		return $this->belongsTo('Message','message_id','id');
	}

	//得到消息数
	public function getMsgNum($id)
	{
		$msg = $this::field('id')->where(['receve_id'=>$id,'is_read'=>0])->column('id');
		if(!is_null($msg)) {
			return count($msg);
		}
	}

	
}