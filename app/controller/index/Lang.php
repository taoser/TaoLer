<?php
// +----------------------------------------------------------------------
// | 状态提示
// +----------------------------------------------------------------------
namespace app\index\controler;

use app\common\controller\BaseController;
use think\facade\Cookie;

class Lang extends BaseController
{
	public function select()
	{
		$lang = input('language');
		//dump($lang);
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
	}
	

}
