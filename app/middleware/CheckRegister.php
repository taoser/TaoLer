<?php

namespace app\middleware;
use think\facade\Db;
use think\facade\Request;

class CheckRegister
{
    public function handle($request, \Closure $next)
    {
		//排除禁止注册用户名的字段
		if($request->action(true)=='reg'){
			$name = $request->param('name');
			$disname = Db::name('system')->where('id',1)->value('blackname');
			$data = explode("|",$disname);
			foreach($data as $v){
				if(stripos($name,$v) !== false){
					return json(['msg'=>'非法字段或该用户名禁止注册,请更换']);
				}
			}
		}
		return $next($request);
    }
}
