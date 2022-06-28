<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-06-19 16:35:30
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \TaoLer\app\listener\UserLogin.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
declare (strict_types = 1);

namespace app\listener;

use think\facade\Db;
use think\facade\Log;
use app\common\model\User;
use think\facade\Lang;
use taoler\com\Api;

class UserLogin
{
    /**
     * 监听登陆，记录IP和日志
     * @param $user
     * @throws \think\db\exception\DbException
     */
    public function handle($user)
    {
		$type = $user->user['type'];
		$id = $user->user['id'];
		
		$u = User::find($id);
		//日志
		if($type == 'log'){
			//$name = $user->user['name'];
			$ip = request()->ip();
			$url = 'http://ip-api.com/json/' . $ip . '?lang=zh-CN&fields=57361';
			$ipJson = Api::urlGet($url);
			if($ipJson->status == 'success'){
				$city = $ipJson->city;
			} else {
				$city ='未知';
			}
			
			$u->allowField(['city','last_login_ip','last_login_time','login_error_num'])->save(
				[
					'city' => $city,
					'last_login_ip'		=> $ip,
					'last_login_time'	=> time(),
					'login_error_num'	=> 0
				]
			);
			Log::channel('login')->info('login:{user} {ip}',['user'=>$u->name,'ip'=>$ip]);
		}
        
		if($type == 'logError'){	
			$res = $u->allowField(['login_error_num','login_error_time'])->save(['login_error_num'=>$u->login_error_num+1,'login_error_time'=>time()]);
		}

    }
}
