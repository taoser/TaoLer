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
use taoler\com\Files;
use taoler\com\Api;

class Set extends AdminController
{
	protected function initialize()
    {
        parent::initialize();
      
    }
	//网站设置显示
	public function index()
    {
		$mailserver = MailServer::find(1);
		$sysInfo = Db::name('system')->find(1);
		$syscy = $this->cyCheck($sysInfo['base_url'],$sysInfo['domain']);
		$template = Files::getDirName('../view');
        View::assign(['sysInfo'=>$sysInfo,'syscy'=>$syscy,'mailserver'=>$mailserver,'template'=>$template]);
		return View::fetch('set/system/website');
    }
	
    //网站设置
    public function website()
    {
		if(Request::isPost()){
			$data = Request::only(['webname','template','cache','upsize','uptype','blackname','webtitle','keywords','descript','icp','copyright']);
			$result = Db::name('system')->cache('system')->where('id', 1)->update($data);
			if($result){
				return json(['code'=>0,'msg'=>'更新成功']);
			} else {
				return json(['code'=>-1,'msg'=>'更新失败']);
			}
		}
    }
	
	//综合设置
	public function server()
	{
		return View::fetch('set/system/server');
	}

    /**邮箱设置
     * parem $id
     */
    public function email()
    {
		$mailserver = MailServer::find(1);
        //邮箱配置
		if(Request::isAjax()){
			$data = Request::only(['host','port','mail','nickname','password']);
			$res = $mailserver->save($data);
			if($res){
				return json(['code'=>0,'msg'=>'更新成功']);
			} else {
				return json(['code'=>-1,'msg'=>'更新失败']);
			}
		}
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
		
}
