<?php
namespace app\middleware;

use think\Request;
use think\facade\Config;
use think\facade\View;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Db;
use think\facade\Cache;
use taoler\com\Files;


class Index
{
	/**
	 * 前置中间件
	 * 检测项目是否安装
	 * 用户登陆及赋值
	 *
	 * @param Request $request
	 * @param \Closure $next
	 * @return void
	 */
    public function handle(Request $request, \Closure $next)
    {
        // 检查是否安装
        $app = $request->pathinfo();
	
		if(!file_exists('./install.lock') && str_starts_with($app, 'install/')){
			return redirect('/install/index');
		}

        // 配置视图路径
        View::config([
            'view_path'			=> app_path() .'index' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $this->getTemplate() . DIRECTORY_SEPARATOR,
			'view_dir_name'		=> 'view' . DIRECTORY_SEPARATOR . $this->getTemplate(),
			'taglib_pre_load'	=> $this->getTaglibPreLoad(),
        ]);

		$userInfo = [];

		// 登录检测
		if(session('?user_id')){
			$userId = session('user_id');
			$userInfo = $this->getUserInfo($userId);
		} else {
			// 未登陆，获取加密的Cookie
			$cooAuth = Cookie::get('auth');
			if(!empty($cooAuth)) {
				$resArr = explode(':',$cooAuth);
				$userId = end($resArr);
				$userInfo = $this->getUserInfo($userId);
				if(!is_null($userInfo)){
					//验证cookie
					$salt = Config::get('taoler.salt');
					$auth = md5($userInfo['name'].$salt).":".$userId;
					if($auth == $cooAuth){
						Session::set('user_name', $userInfo['name']);
						Session::set('user_id', $userId);
					}
				}
			}
		}

		$request->user = $userInfo;
		$request->isLogin = !empty($userInfo);
		View::assign('user', $userInfo);

		return $next($request);
    }

	protected function getUserInfo(int $userId)
	{
		$user = Db::name('user')
			->alias('u')
			->join('user_viprule v', 'v.vip = u.vip')
			->field('u.id as id,v.id as vid,name,nickname,user_img,sex,area_id,auth,city,phone,email,active,sign,point,u.vip as vip,nick,u.create_time as create_time')
			->cache(true)
			->find($userId);

		return $user;
	}

	/**
	 * 获取模板名称
	 * @return string
	 */
	protected function getTemplate() : string
	{
		
		return Db::name('system')->where('id',1)->cache(true)->value('template');
		
	}

	protected function getTaglibPreLoad()
	{
		$taglib_pre_load = Cache::remember('taglib', function(){
			$tagsArr = [];
			//获取应用公共标签app/common/taglib
			$common_taglib = Files::getAllFile(root_path().'app/common/taglib');
			foreach ($common_taglib as $t) {
				$tagsArr[] = str_replace('/','\\',strstr(strstr($t, 'app/'), '.php', true));
			}

			//获取插件下标签 addons/taglib文件
			$localAddons = Files::getDirName('../addons/');
			foreach($localAddons as $v) {
				$dir = root_path() . 'addons'. DIRECTORY_SEPARATOR . $v . DIRECTORY_SEPARATOR .'taglib';
				if(!file_exists($dir)) continue;
				$addons_taglib = Files::getAllFile($dir);
				foreach ($addons_taglib as $a) {
					$tagsArr[] = str_replace('/','\\',strstr(strstr($a, 'addons'), '.php', true));
				}
			}
			
			return implode(',', $tagsArr);
		});

		return $taglib_pre_load;
	}
}