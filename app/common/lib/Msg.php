<?php
// +----------------------------------------------------------------------
// | 状态提示
// +----------------------------------------------------------------------
namespace app\common\lib;

use think\facade\Lang;

class Msg
{
	public static function getCode($strCode){
		//状态配置
		$res = [
		'success'		=> 1,
		'error'			=> 0,
		
		];
		
		foreach($res as $k => $v){
			if($k == $strCode){
				return $res = $v;
			}
		}
	//return $res;
	}
	
	public static function getMsg($strMsg){
		//状态配置
		$res = [
		'add_success'	=> Lang::get('add success'),
		'add_error'		=> Lang::get('add error'),
		'edit_success'	=> Lang::get('edit success'),
		'edit_error'	=> Lang::get('edit error'),
		'illegal_request'	=> Lang::get('illegal request'),
		
		];
		
		foreach($res as $k => $v){
			if($k == $strMsg){
				return $res = $v;
			}
		}
	 //$res;
	}
	
	public static function show($strCode,$strMsg,$url)
	{
		$res = [
			'code'	=> self::getCode($strCode),
			'msg'	=> self::getMsg($strMsg),
			'url'	=> $url,
		];
		
		return json($res);
	}
	

}
