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
		$this->sysInfo = $this->getSystem();
    }
	//网站设置显示
	public function index()
    {
		$mailserver = MailServer::find(1);
		$template = Files::getDirName('../view');
        View::assign(['sysInfo'=>$this->sysInfo,'mailserver'=>$mailserver,'template'=>$template]);
		return View::fetch('set/system/website');
    }
	
    //网站设置
    public function website()
    {
		if(Request::isPost()){
			$data = Request::only(['webname','domain','template','cache','upsize','uptype','blackname','webtitle','keywords','descript','icp','showlist','copyright']);
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

	//上传logo
	public function upload()
	{
        $uploads = new \app\common\lib\Uploads();
        $upRes = $uploads->put('file','logo',2000,'image','uniqid');
        $logoJson = $upRes->getData();
		if($logoJson['status'] == 0){
			$result = Db::name('system')->where('id', 1)->update(['logo'=>$logoJson['url']]);
			if($result){
				$res = ['code'=>0,'msg'=>'上传logo成功'];
			} else {
				$res = ['code'=>1,'msg'=>'上传错误'];
			}
        }
	return json($res);
	}
		
}
