<?php

namespace taoler\com;

class Api
{
	public static function urls($url)
	{
		if($url == ''){
			return json(['code'=>-1,'msg'=>'800']);
		}
		$ch =curl_init ();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch,CURLOPT_POST, 1);
		$data = curl_exec($ch);
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpCode == '200'){
			return json_decode($data);
		} else {
			return json(['code'=>-1,'msg'=>'Ô¶³Ì·þÎñÆ÷Ê§°Ü']);
		}
	}
}