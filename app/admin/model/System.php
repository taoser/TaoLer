<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\model\System.php
 * @Description: System
 * @LastEditTime: 2023-03-14 16:52:00
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\model;

use think\Model;
use think\facade\Cache;

class System extends Model
{
	
	//登陆校验
    public function sets($data,$cy)
    {	
		$sys = $this::find(1);
		if($cy == 0){
			unset($data['copyright']);
		}
		$res = $sys->save($data);
		if($res){
			Cache::delete('system'); 
			return 1;
		} else {
			return Lang::get('edit error');
		}
    }
}
