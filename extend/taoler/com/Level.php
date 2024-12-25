<?php

namespace taoler\com;

use think\facade\Db;
use app\index\model\Vip;
use app\index\model\User;

class Level
{	
	//根据用户积分升级vip等级
	public static function writeLv($uid)
	{
		$user = User::find($uid);
		$score = $user->point;
		$userLv = $user->vip;

		$vipLv = self::getLevel($score);
		if($vipLv>$userLv){
			$result = User::update(['id'=>$uid,'vip'=>$vipLv]);
			if($result){
				return true;
			}else{
				return false;
			}
		}
    }
	
	//根据积分获取用户vip等级
	public static function getLevel($point)
	{
		$vip = Db::name('user_viprule')->select();
		foreach($vip as $k => $v){
			$score = explode('-',$v['score']);
			if($score[0] <= $point && $point <= $score[1]){
				return $v['vip'];
			}
		}
	}

}