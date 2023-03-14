<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-07-24 11:06:14
 * @LastEditors: TaoLer
 * @Description: 设置
 * @FilePath: \TaoLer\app\admin\controller\Set.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\system;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Config;
use app\admin\model\System;
use app\admin\model\MailServer;
use taoler\com\Files;
use think\facade\Session;
use think\facade\Cookie;
use taoser\SetArr;
use app\common\lib\SetArr as SetArrConf;
use think\response\Json;

class Set extends AdminController
{

	protected $sysInfo = '';
	
	public function __construct()
	{
        parent::initialize();
		$this->sysInfo = $this->getSystem();
	}

	//网站设置显示
	public function index()
    {
		$template = Files::getDirName('../view');
		$email = Db::name('admin')->where('id',1)->value('email');

		// 应用映射
		$index_map = array_search('index',config('app.app_map'));
		$admin_map = array_search('admin',config('app.app_map'));
		$index_map = $index_map ? $index_map : '';
		$admin_map = $admin_map ? $admin_map : '';
        View::assign(['sysInfo'=>$this->sysInfo,'template'=>$template,'email'=>$email,'index_map'=>$index_map,'admin_map'=>$admin_map]);
		
		// 域名绑定
		if(!empty(config('app.domain_bind'))){
			$data = array_flip(config('app.domain_bind'));
			$domain_bind = [
				'index' => isset($data['index']) ? $data['index'] : '',
				'admin' => isset($data['admin']) ? $data['admin'] : '',
			];
		} else {
			$domain_bind = [
				'index' => '',
				'admin' => '',
			];
		}
		View::assign($domain_bind);

		// url美化
		$urlArr = config('taoler.url_rewrite');
		$urlRe = [];
		foreach($urlArr as $k => $v) {
			if(!empty($v)) {
				$urlRe[$k] = substr($v, 0, strrpos($v, '/'));
			} else {
				$urlRe[$k] = '';
			}
 		}
		 
		 View::assign('url_re',$urlRe);

		return View::fetch();
    }
	
    //网站设置
    public function website()
    {
		if(Request::isPost()){
			$data = Request::only(['webname','domain','template','cache','upsize','uptype','blackname','webtitle','keywords','descript','state','icp','showlist','copyright']);
			$system = new System();
			$result = $system->sets($data,$this->sysInfo['clevel']);
			if($result == 1){
				return json(['code'=>0,'msg'=>'更新成功']);
			} else {
				return json(['code'=>-1,'msg'=>$result]);
			}
		}
    }
	
	//综合设置
	public function server()
	{
		return View::fetch('set/system/server');
	}

	/**基础服务配置
     * parem $id
     */
    public function config()
    {
		$conf = Config::get('taoler.config');
		if(Request::isPost()){
			$data = Request::param();
			if(!isset($data['regist_check'])) $data['regist_check'] =1;
			if(!isset($data['posts_check'])) $data['posts_check'] =1;
			if(!isset($data['commnets_check'])) $data['commnets_check'] =1;
			foreach($conf as $c=>$f){
				if(array_key_exists($c,$data)){
					$conf[$c] = (int) $data[$c];
				}else{
					$conf[$c] = 0;
				}
			}

			$value = [
				'config'=>$conf
			];

			$result = SetArr::name('taoler')->edit($value);
			if($result){
				$res = ['code'=>0,'msg'=>'配置成功'];
			} else {
				$res = ['code'=>-1,'msg'=>'配置出错！'];
			}
			return json($res);
		}
    }

	// 域名绑定
	public function setDomain()
	{
		$str = file_get_contents(str_replace('\\', '/', app()->getConfigPath() . 'app.php'));
		if(Request::isPost()){
			$data = Request::only(['index','admin','domain_check']);
			//$data = Request::param();
			//dump($data);
			if($data['domain_check'] == 'on') {
				
				// 过滤空项目
				$domain_bind = [];
				if(!empty($data['index'])){
					$domain_bind[$data['index']] ='index';
					if(config('app.default_app') == $domain_bind[$data['index']]) {
						if(empty($data['admin'])) {
							return json(['code'=>-1, 'msg'=>'默认应用和Index一致时必须绑定Admin域名,否则无法进入后台']);
						}
					}
				}
				if(!empty($data['admin'])){
					$domain_bind[$data['admin']] ='admin';
				}


				// 匹配整个domain_map数组
				$pats_domain_bind = '/\'(domain_bind)\'[^\]]*\],/';
				// 	空数组
				$rep_domain_null = "'domain_bind'\t=> [\n\t],";
				$str = preg_replace($pats_domain_bind, $rep_domain_null, $str);

				// 匹配'domain_bind' => [
				$pats = '/\'(domain_bind)\'\s*=>\s*\[\r?\n/';
				preg_match($pats,$str,$arr);

				// 拼接数组内字符串
				$domainArr = '';
				foreach($domain_bind as $k => $v){
					$domainArr .= "\t\t'". $k. "'   => '" . $v . "',\n";
				}

				// 追加组成新数组写入文件
				$reps = $arr[0].$domainArr;
				$str = preg_replace($pats, $reps, $str);

				$res = file_put_contents(app()->getConfigPath() . 'app.php', $str);

				// 如果编辑了后台 ，需要清理退出缓存
				if(!empty($domain_bind[$data['admin']])) {
					//清空缓存
					Cookie::delete('adminAuth');
					Session::clear();
				}
			} else {
				$res = SetArr::name('app')->delete([
					'domain_bind'=> config('app.domain_bind'),
				]);		
			}
			
			if($res == true){
				return json(['code'=>0,'msg'=>'成功']);
			} else{
				return json(['code'=>-1,'msg'=>'失败']);
			}
		}
		
	}

	/**
	 * 绑定应用映射
	 *
	 * @return void
	 */
	public function bindMap()
	{
		$data = Request::only(['index_map','admin_map']);
		$str = file_get_contents(str_replace('\\', '/', app()->getConfigPath() . 'app.php'));

		// 过滤空项目
		$app_map = [];
		if(!empty($data['index_map'])){
			$app_map[$data['index_map']]='index';
		}
		if(!empty($data['admin_map'])){
			$app_map[$data['admin_map']] ='admin';
		}

		//halt($app_map);
		// $set = new SetArrConf('app');
		// $res = $set->delete(['app_map' => config('app.app_map')])->add([
		// 	'app_map' => $app_map,
		// ])->put();

		// halt($res);

		// 匹配整个app_map数组
		$pats_app_map = '/\'(app_map)\'[^\]]*\],/';
		preg_match($pats_app_map,$str,$arr_map);

		// 	空数组
		$rep_map_null = "'app_map'\t=> [\n\t],";
		$str = preg_replace($pats_app_map, $rep_map_null, $str);

		// 匹配'app_map' => [
		$pats = '/\'(app_map)\'\s*=>\s*\[\r?\n/';
		preg_match($pats,$str,$arr);

		// 拼接数组内字符串
		$appArr = '';
		foreach($app_map as $k => $v){
			$appArr .= "\t\t'". $k. "'   => '" . $v . "',\n";
		}

		// 追加组成新数组写入文件
		$reps = $arr[0].$appArr;
		$str = preg_replace($pats, $reps, $str);

		$res = file_put_contents(app()->getConfigPath() . 'app.php', $str);

		if(!$res) {
			return json(['code'=>-1,'msg'=>'绑定失败']);
		}
		return json(['code'=>0,'msg'=>'绑定成功']);
		
	}

    /**
     * URL美化，设置访问链接
     * @return Json
     */
	public function setUrl()
	{
		$data = Request::only(['article_as','cate_as']);
		$arr = [];
		foreach($data as $k => $v) {
			if(!empty($v)) {
				$arr['url_rewrite'][$k] = $v . '/';
			} else {
				$arr['url_rewrite'][$k] = '';
			}
		}
		// if(empty($arr['url_rewrite']['cate_as'])) return json(['code'=>-1,'msg'=>'分类不能为空']);

		if(!array_key_exists('url_rewrite',config('taoler'))){
			$result = SetArr::name('taoler')->add($arr);
		} else {
			$result = SetArr::name('taoler')->edit($arr);
		}
		if($result){
			$res = ['code'=>0,'msg'=>'配置成功'];
		} else {
			$res = ['code'=>-1,'msg'=>'配置出错！'];
		}
		return json($res);

	}

	//上传logo
	public function upload()
	{
		$param = Request::param('field');
        $uploads = new \app\common\lib\Uploads();
        $upRes = $uploads->put('file','SYS_logo',2000,'image');
        $logoJson = $upRes->getData();
		if($logoJson['status'] == 0){
			if($param == 'logo'){
				$result = Db::name('system')->where('id', 1)->cache('system')->update(['logo'=>$logoJson['url']]);
			} else {
				//移动端logo
				$result = Db::name('system')->where('id', 1)->cache('system')->update(['m_logo'=>$logoJson['url']]);
			}
			
			if($result){
				$res = ['code'=>0,'msg'=>'更新logo成功'];
			} else {
				$res = ['code'=>-1,'msg'=>'上传成功，数据无须更新'];
			}
        } else {
			$res = ['code'=>-1, 'msg'=>$logoJson['msg']];
		}
		return json($res);
	}
		
}
