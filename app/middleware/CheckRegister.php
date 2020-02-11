<?php

namespace app\middleware;
use think\facade\Db;
use think\facade\Request;

class CheckRegister
{
    public function handle($request, \Closure $next)
    {
		//排除特殊注册用户名
		if($request->action(true)=='reg'){
			$disname = Db::name('system')->where('id',1)->value('blackname');
			$data = explode("|",$disname);
			foreach($data as $v){
				if ($request->param('name') == $v) {
				return json(['msg'=>'该用户名禁止注册，请更换名称']);
				}
			}
		}
		return $next($request);
    }
}
