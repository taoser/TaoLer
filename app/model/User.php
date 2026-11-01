<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-11-01 10:49:11
 * @LastEditors: TaoLer
 * @Description: 用户模型
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\model\User.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\model;

use think\model\concern\SoftDelete;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Config;
use think\facade\Lang;
use app\event\UserLogin;
use app\common\helper\FileHelper;
use app\common\helper\JwtAuth;
use Exception;

class User extends BaseModel
{
    //软删除
    use SoftDelete;

    protected function getOptions(): array 
    {
        return [
            'readonly' =>  ['name'],
            'deleteTime' => 'delete_time',
            'defaultSoftDelete' => null,
        ];
    }

    protected $loggedUser;
	
	//用户关联评论
	public function comments()
	{
		return $this->hasMany('Comment','user_id','id');
	}
	
	//用户关联所属区域
    public function userArea()
    {
        return $this->belongsTo('UserArea','user_raea_id','id');
    }

    public function article()
    {
        return $this->hasMany(Article::class);
    }

    // 登录用户
    public function getLoggedUser(){
        return $this->loggedUser;
    }
	
	
}