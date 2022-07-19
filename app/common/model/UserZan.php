<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-18 18:32:40
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \TaoLer\app\common\model\UserZan.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\common\model;

use think\Model;
//use think\model\concern\SoftDelete;

class UserZan extends Model
{
	protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
    
	
	//软删除
	//use SoftDelete;
	//protected $deleteTime = 'delete_time';
	//protected $defaultSoftDelete = 0;
	
	public function comment()
	{
		//评论关联文章
		return $this->belongsTo('Comment','comment_id','id');
	}
	
	public function user()
	{
		//评论关联用户
		return $this->belongsTo('User','user_id','id');
	}

	public function article()
	{
		//评论关联用户
		return $this->belongsTo(Article::class);
	}
	
}