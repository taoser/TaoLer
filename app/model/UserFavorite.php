<?php

namespace app\model;

class UserFavorite extends BaseModel
{
	protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';
	
	/**
	 * 收藏1对多相对关联用户
	 */
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * 收藏1对多相对关联文章
	 */
	public function article()
	{
		return $this->belongsTo(Article::class);
	}
	
}