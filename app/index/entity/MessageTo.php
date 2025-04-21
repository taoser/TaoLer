<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-08-16 12:01:52
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \TaoLer\app\common\model\MessageTo.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */

 namespace app\index\entity;

use app\common\entity\BaseEntity;

class MessageTo extends BaseEntity
{

	//得到消息数
	public function getMsgNum($id)
	{
		$msgNum = $this::where(['receve_id'=>$id,'is_read'=>0])->count('id');
		if($msgNum) {
			return $msgNum;
		}
		return 0;
	}

	
}