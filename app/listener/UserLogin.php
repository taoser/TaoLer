<?php
declare (strict_types = 1);

namespace app\listener;

use think\facade\Db;
use think\facade\Log;
use app\common\model\User;
use think\facade\Lang;

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
		
			/*
			$url = 'http://ip-api.com/json/'.$ip.'?lang=zh-CN';
							//$url = 'http://ip-api.com/json/?lang=zh-CN';
							$add = Api::urlGet($url);
							if($add->status == 'success'){
								$city = $add->city;
							} else {
								$city ='未知';
							}
			*/

			$u->allowField(['last_login_ip','last_login_time','login_error_num'])->save(
				[
					//'city' => $city,
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
