<?php

namespace taoler\com;

class Str
{
	// 隐藏部分字符串
	public static function func_substr_replace($str, $replacement = '*', $start = 5, $length = 30)
	{
		$len = mb_strlen($str,'utf-8');
		if ($len > intval($start+$length)) {
			$str1 = mb_substr($str,0,$start,'utf-8');
			$str2 = mb_substr($str,intval($start+$length),NULL,'utf-8');
		} else {
			$str1 = mb_substr($str,0,1,'utf-8');
			$str2 = mb_substr($str,$len-1,1,'utf-8');   
			$length = $len - 2;       
		}

		$new_str = $str1;
		for ($i = 0; $i < $length; $i++) {
			$new_str .= $replacement;
		}
		$new_str .= $str2;
		return $new_str;

	}
}