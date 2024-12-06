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
namespace app\index\model;

use Exception;
use app\common\model\BaseModel;

class UserZan extends BaseModel
{
	protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
	
	public function comment()
	{
		//关联评论
		return $this->belongsTo('Comment','comment_id','id');
	}
	
	public function user()
	{
		//关联用户
		return $this->belongsTo('User','user_id','id');
	}

	public function article()
	{
		//关联文章
		return $this->belongsTo(Article::class);
	}
	
}