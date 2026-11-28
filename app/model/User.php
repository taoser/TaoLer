<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-11-28 17:14:03
 * @LastEditors: TaoLer
 * @Description: 用户模型
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\model\User.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\model;

use think\model\concern\SoftDelete;

class User extends BaseModel
{
    //软删除
    use SoftDelete;

    protected function getOptions(): array 
    {
        return [
            'readonly'          =>  ['name'],
            'deleteTime'        => 'delete_time',
            'defaultSoftDelete' => null,
        ];
    }

    protected $loggedUser;

    // 用户1对1关联所属区域
    public function userArea()
    {
        return $this->belongsTo('UserArea','user_raea_id','id');
    }

    /**
     * 用户一对多关联文章
     */
    public function article()
    {
        return $this->hasMany(Article::class);
    }

    /**
     * 用户一对多关联文章
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
	
	/**
     * 用户一对多关联评论
     */
	public function comments()
	{
		return $this->hasMany(UserComment::class,'user_id','id');
	}
	

    /**
     * 用户一对多关联收藏
     */
    public function favorites()
    {
        return $this->hasMany(UserFavorite::class);
    }

    

    // 登录用户
    public function getLoggedUser(){
        return $this->loggedUser;
    }
	
	
}