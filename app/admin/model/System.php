<?php

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
