<?php
// +----------------------------------------------------------------------
// | 状态提示
// +----------------------------------------------------------------------
namespace app\common\controller;

use think\facade\Cookie;

class Language
{
	public function select($lang)
	{
		switch ($lang) {
			case 'cn':
				Cookie::set('think_lang','zh-cn');
				break;
			case 'en':
				Cookie::set('think_lang','en-us');
				break;
			case 'tw':
				Cookie::set('think_lang','zh-tw');
				break;
			default:
				break;
			}
		return true;
	}
	

}
