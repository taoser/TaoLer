<?php
/*
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-31 13:06:34
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \github\TaoLer\app\listener\UserLogin.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
declare (strict_types = 1);

namespace app\listener;

use think\facade\Log;
use app\common\model\User;
use app\common\lib\facade\HttpHelper;

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
        $ip = request()->ip();
        $url = 'http://ip-api.com/json/' . $ip . '?lang=zh-CN&fields=57361';
        $city = 'earth';

        //日志
        if($type == 'log'){
            //$name = $user->user['name'];

            try{
                $ipInfo = HttpHelper::get($url)->toJson();
                if($ipInfo->status == 'success')
                {
                    $city = $ipInfo->city;
                }
            } catch (\Exception $e) {
                // echo $e->getMessage();
            }

            //国内查询，接口已失效
//          $url = 'http://freeapi.ipip.net/' . $ip;
//			$ipJson = Api::urlGetRespond($url);
//			$respond = $ipJson->getData();
//			if($respond['code'] == 0){
//				//字符串数组["中国","北京","北京"]
//				$data = $respond['data'];
//				//正则去掉[''],保留字符串
//				$str = preg_replace('/(\"|\[|\])/','',$data);
//				//地址数组
//				$arr = explode(',', $str);
//                $city = 'earth';
//				if($arr[0] !== '本机地址') {
//					$city = $arr[2];
//				}
//			}

        }

        if($type == 'logError'){
            $u->allowField(['login_error_num','login_error_time'])->save(['login_error_num'=>$u->login_error_num + 1,'login_error_time'=>time()]);
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
}
