<?php 
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-11-28 18:23:00
 * @LastEditors: TaoLer
 * @Description: 评论门面
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\facade\Comment.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\facade;

use think\Facade;

/**
 ** @see \app\model\Comment
 * @package think\facade
 * @method static Json add() 添加评论
 */
class Comment extends Facade
{
	protected static function getFacadeClass()
	{
		return 'app\entity\UserComment';
	}



}