<?php
declare (strict_types = 1);

namespace app\common\lib;

use think\facade\Lang;

class Msgres
{
	static protected $res = [];

    /**
     * 设置状态吗
     * @return array
     */
	public static function setCodes()
    {
        return $res = [
            'success'           => 0,
            'error'             => 1,
            'add_success'       => Lang::get('add success'),
            'add_error'         => Lang::get('add error'),
            'edit_success'      => Lang::get('edit success'),
            'edit_error'        => Lang::get('edit error'),
            'delete_success'    => Lang::get('delete success'),
            'delete_error'      => Lang::get('delete error'),
            'upload_success'   => Lang::get('upload success'),
            'upload_error'     => Lang::get('upload error'),
            'upgrade_success'   => Lang::get('upgrade success'),
            'upgrade_error'     => Lang::get('upgrade error'),
            'illegal_request'   => Lang::get('illegal request'),
        ];
    }


    /**
     * 获取返回码
     * @param string $strCode
     * @return mixed string
     */
	public static function getCode(string $strCode){
		foreach(self::setCodes() as $k => $v){
			if($k == $strCode){
				return $v;
			}
		}
	}

    /**
     * 获取返回信息 如果不存在返回自身
     * @param string $strMsg
     * @return mixed|string
     */
	public static function getMsg(string $strMsg){
        if(empty($strMsg)){
            return '';
        };
		foreach(self::setCodes() as $k => $v){
			if($k == $strMsg){
				return $v;
			}
		}
		return $strMsg;
	}

    /**
     * 成功提示
     * @param string $strMsg
     * @param string|null $url
     * @param string $data
     * @return string|\think\response\Json
     */
	public static function success(string $strMsg = '',string $url = null, $data = '') {
		$result = [
            'code' => self::getCode('success'),
            'msg' => self::getMsg($strMsg),
            'url' => $url,
            'data' => $data
        ];
		return json($result);
	}

    /**
     * 失败提示
     * @param string $strMsg 消息提示码
     * @param string|null $url 跳转地址
     * @param string $data  返回数据
     * @return string|\think\response\Json
     */
	public static function error(string $strMsg = '',string $url = null, $data = ''){
        $result = [
            'code' => self::getCode('error'),
            'msg' => self::getMsg($strMsg),
            'url' => $url,
            'data' => $data
        ];
        return json($result);
	}


}
