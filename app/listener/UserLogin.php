<?php
declare (strict_types = 1);

namespace app\listener;

use think\facade\Db;
use think\facade\Log;

class UserLogin
{
    /**
     * 监听登陆，记录IP和日志
     * @param $user
     * @throws \think\db\exception\DbException
     */
    public function handle($user)
    {
        $name = $user->name;
        $ip = $user->ip;
		
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

        Db::name('user')->where('name',$name)->update(
            [
                //'city' => $city,
                'last_login_ip' => $user->ip,
                'last_login_time' => time()
            ]
        );
        Log::channel('login')->info('login:{user} {ip}',['user'=>$name,'ip'=>$ip]);

    }
}
