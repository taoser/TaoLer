<?php
namespace app\middleware;

use think\Request;
use think\Response;
use think\facade\View;
use think\facade\Db;
use think\facade\Cache;

class Index
{
	/**
	 * Index模块 前置中间件
	 * 检测项目是否安装
	 * 配置视图路径/模板/标签库预加载
	 * 从session中获取用户ID 存储到$request中
	 *
	 * @param Request $request
	 * @param \Closure $next
	 * @return void
	 */
    public function handle(Request $request, \Closure $next)
    {
        // 检查系统是否安装
        $path = $request->pathinfo();
		if(!file_exists(public_path().'install.lock') && !str_starts_with($path, 'install/')) {
			return redirect('/install/index');
		}

        // 配置视图路径/模板/标签库预加载
        View::config([
            // 'view_path'			=> app_path() .'index' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $this->getTemplate() . DIRECTORY_SEPARATOR,
			'view_path'			=> root_path()  . 'view' . DIRECTORY_SEPARATOR . $this->getTemplate() . DIRECTORY_SEPARATOR,
			'view_dir_name'		=> 'view' . DIRECTORY_SEPARATOR . $this->getTemplate(),
			'taglib_pre_load'	=> $this->setTaglibPreLoad(),
        ]);

		// 从session中获取用户ID 存储到$request中
		$request->uid = $request->session('user_id');

		return $next($request);
    }

	/**
	 * 获取模板名称
	 * @return string
	 */
	protected function getTemplate() : string
	{
		return Db::name('system')->where('id',1)->cache(true)->value('template');
	}

	/**
	 * 预加载视图view标签库
	 * @return string
	 */
	protected function setTaglibPreLoad() : string
	{
		$taglib_pre_load = Cache::remember('taglib', function() {
			$rootPath = root_path();
			$tagsArr = [];
			//获取应用公共标签app/common/taglib
			// $commonTaglibs = FileHelper::getDirFilePaths(root_path().'app/common/taglib');
			$commonTaglibs = glob($rootPath . 'app/common/taglib/*.php');
			foreach ($commonTaglibs as $t) {
				$tagsArr[] = str_replace('/','\\', strstr(strstr($t, 'app/'), '.php', true));
			}

			//获取插件下标签 addons/taglib文件
			$addonsDir = $rootPath . 'addons' . DIRECTORY_SEPARATOR;
			// $localAddons = FileHelper::getSubDirNames($addonsDir);
			$localAddons = array_filter(scandir($addonsDir), function($dir) use ($addonsDir) {
				return $dir !== '.' && $dir !== '..' && is_dir($addonsDir . $dir);
			});
			
			foreach($localAddons as $name) {
				$addonDir = $addonsDir . $name . DIRECTORY_SEPARATOR .'taglib' . DIRECTORY_SEPARATOR;
				if(!file_exists($addonDir)) continue;

				// $taglibs = FileHelper::getDirFilePaths($addonDir);
				$taglibs = glob($addonDir . '*.php');
				foreach ($taglibs as $a) {
					$tagsArr[] = str_replace('/','\\', strstr(strstr($a, 'addons'), '.php', true));
				}
			}

			return empty($tagsArr) ? '' : implode(',', array_unique($tagsArr));
	
		}, 1800);

		return $taglib_pre_load;
	}

	/**
	 * 获取文件命名空间类路径 
	 * @param string $filepath 完整文件路径 E:\github\TaoLer\app\common\taglib\Index.php
	 * @return string app\common\taglib\Index
	 */
	protected function getFileClassPath(string $filepath): string
	{
		$rootPath = root_path();
		$info = pathinfo($filepath);
		$str = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'];
		return str_replace([$rootPath, DIRECTORY_SEPARATOR], ['','\\'], $str);
	}


}