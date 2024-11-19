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

        // 登录日志
        Log::channel('login')->info('login:{user} {ip}',['user'=>$u->name,'ip'=>$ip]);

        //日志
        if($type == 'log'){
            // try{
            //     $ipInfo = HttpHelper::get($url)->toJson();
            //     if($ipInfo->status == 'success') {
            //         $city = $ipInfo->city;
            //     }

            // } catch (\Exception $e) {
            //     // echo $e->getMessage();
            // }
<<<<<<< HEAD
            
=======

>>>>>>> 3.0
            $data = [
                'city' => $city,
                'last_login_ip'		=> $ip,
                'last_login_time'	=> time(),
                'login_error_num'	=> 0
            ];
        }

        // 登录失败 失败次数加1
        if($type == 'logError') {
            $data = [
                'login_error_num'   => $u->login_error_num + 1,
                'login_error_time'  => time()
            ];
        }

        $u->allowField(['city','last_login_ip','last_login_time','login_error_num'])->save($data);
    }
}
