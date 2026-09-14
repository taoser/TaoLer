<?php

namespace app\common\controller;

use think\facade\Cookie;

class Language
{
	public function select($lang)
	{
		switch ($lang) {
			case 'zh-cn':
				Cookie::set('think_lang','zh-cn');
				break;
			case 'en-us':
				Cookie::set('think_lang','en-us');
				break;
			case 'zh-tw':
				Cookie::set('think_lang','zh-tw');
				break;
			default:
				Cookie::set('think_lang','zh-cn');
				break;
			}
		return true;
	}
	

}
