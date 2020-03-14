<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\admin\model\System;
use app\admin\model\MailServer;
use think\facade\Config;
use think\exception\ValidateException;

class Set extends AdminController
{
	protected function initialize()
    {
        parent::initialize();
      
    }
	
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function website()
    {
		if(Request::isAjax()){
			$data = Request::param();
			unset($data['file']);
			//$system = System::find(1);
			//$result = $system->allowField(['webname','webtitle','domain','keywords','descript','copyright','blackname'])->save($data);
			$result = Db::name('system')->cache('system')->where('id', 1)->update($data);
			if($result){
				return json(['code'=>0,'msg'=>'更新成功']);
			} else {
				return json(['code'=>-1,'msg'=>'更新失败']);
			}
		}
		$sysInfo = Db::name('system')->find(1);
		$syscy = $this->check($sysInfo['base_url']);
        View::assign(['sysInfo'=>$sysInfo,'syscy'=>$syscy]);
		return View::fetch('set/system/website');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function email()
    {
		//$mailserver = Db::name('mail_server')->find(1);
		$mailserver = MailServer::find(1);
        //邮箱配置
		if(Request::isAjax()){
			$data = Request::param();
			$res = $mailserver->save($data);
			//dump($data);
			if($res){
				return json(['code'=>0,'msg'=>'更新成功']);
			} else {
				return json(['code'=>-1,'msg'=>'更新失败']);
			}
		}
		
		View::assign('mailserver',$mailserver);
		return View::fetch('set/system/email');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
	
	//上传logo
	public function upload()
	{
		$file = request()->file('file');

		try {
			validate(['image'=>'filesize:2048|fileExt:jpg,png,gif|image:200,200,jpg'])
            ->check(array($file));
			$savename = \think\facade\Filesystem::disk('public')->putFile('logo',$file);
		} catch (think\exception\ValidateException $e) {
			echo $e->getMessage();
		}
		$upload = Config::get('filesystem.disks.public.url');
		
		if($savename){
            $name_path =str_replace('\\',"/",$upload.'/'.$savename);
			$result = Db::name('system')->where('id', 1)->update(['logo'=>$name_path]);
			if($result){
				$res = ['code'=>0,'msg'=>'上传logo成功'];
			} else {
				$res = ['code'=>1,'msg'=>'上传错误'];
			}
            
        }
	return json($res);
	}
	
	public function check($url)
	{
		$url = $url.'?u='.Request::domain();
		$ch =curl_init ();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch,CURLOPT_POST, 1);
		$data = curl_exec($ch);
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpCode == '200'){
			$cy = json_decode($data);
			if($cy->code != 0){
				$cylevel = $cy->level;
			return $cylevel;
			} else {
			return 0;
			}
		} else {
			return 0;
		}
	}
		
}
