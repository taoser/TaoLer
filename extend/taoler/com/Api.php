<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-08-01 10:09:11
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \github\TaoLer\extend\taoler\com\Api.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */

namespace taoler\com;
use think\Response;

class Api
{
	public static function urlPost($url, $data)
	{
		if($url == ''){
			return json(['code'=>-1,'msg'=>'800']);
		}
		$ch =curl_init ();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);           // 显示返回的Header区域内容
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);         // 设置超时限制 防止死循环
        
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   // 获取的信息以文件流的形式返回
		$res = curl_exec($ch);
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpCode == '200'){
			return json_decode($res);
		} else {
			return json_decode('{"code":-1,"msg":"远程服务器失败,稍后重试"}');	//转换为对象
		}
	}
	
	public static function urlGet($url)
	{
		$ch =curl_init ();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);	//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。 1表示传输数据，为0表示直接输出显示。
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch, CURLOPT_HEADER, false); //启用时会将头文件的信息作为数据流输出。 参数为1表示输出信息头,为0表示不输出
		$data = curl_exec($ch);
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpCode == '200'){
			return json_decode($data);
		} else {
			//$status ='{"code":-1,"msg":"远程服务器失败"}';	//字符串
			return json_decode('{"code":-1,"msg":"远程服务器失败,稍后重试"}');	//转换为对象
		}
	}

	public static function urlGetRespond($url)
	{
		$ch =curl_init ();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);	//将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。 1表示传输数据，为0表示直接输出显示。
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch, CURLOPT_HEADER, false); //启用时会将头文件的信息作为数据流输出。 参数为1表示输出信息头,为0表示不输出
		$data = curl_exec($ch);
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpCode == '200'){
			//return json_decode($data);
            return json(['code'=>0, 'data'=>$data]);
		} else {
			//return json_decode('{"code":-1,"msg":"远程服务器失败,稍后重试"}');	//转换为对象
			return json(['code'=>-1,'msg'=>'Remote server failed, try again later']);
		}
	}
	
	public static function get_real_ip()
	{
		static $realip;
		if (isset($_SERVER)) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$realip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$realip = $_SERVER['REMOTE_ADDR'];
			}
		} else {
			if (getenv('HTTP_X_FORWARDED_FOR')) {
				$realip = getenv('HTTP_X_FORWARDED_FOR');
			} else if (getenv('HTTP_CLIENT_IP')) {
				$realip = getenv('HTTP_CLIENT_IP');
			} else {
				$realip = getenv('REMOTE_ADDR');
			}
		}
    return $realip;
	}
}