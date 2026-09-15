<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-07-30 07:19:57
 * @LastEditTime: 2026-09-14 21:04:58
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\common\controller\Language.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\common\controller;

use think\facade\Cookie;

class Language
{
	public function select($lang)
	{
		Cookie::set('think_lang',$lang);
		
		return true;
	}
	

}
