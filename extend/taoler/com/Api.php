<?php

namespace taoler\com;

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
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$res = curl_exec($ch);
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpCode == '200'){
			return json_decode($res);
		} else {
			return json(['code'=>-1,'msg'=>'远程服务器失败']);
		}
	}
	
	public static function urlGet($url)
	{
		$ch =curl_init ();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。 参数为1表示输出信息头,为0表示不输出
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
		$data = curl_exec($ch);
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpCode == '200'){
			return json_decode($data);
		} else {
			//return json(['code'=>-1,'msg'=>'远程服务器失败']);
            return false;
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