<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\exception\ValidateException;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Config;
use app\admin\model\System;
use app\admin\model\MailServer;
use taoler\com\Files;
use taoler\com\Api;
use app\common\lib\SetConf;

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
		$email = Db::name('admin')->where('id',1)->value('email');
        View::assign(['sysInfo'=>$this->sysInfo,'mailserver'=>$mailserver,'template'=>$template,'email'=>$email]);
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
	
	 public function sendMailCode()
	 {
		 if(Request::isPost()){
			$email = Request::param('email');
			$code = mt_rand('1111','9999');
			Cache::set('test_code',$code,600);
			$result = mailto($email,'邮箱服务配置','Hi亲爱的管理员:</br>您正在配置您站点的邮箱服务，配置成功后，可以收到来自网站的发帖，评论等即时信息。请在10分钟内把激活码填入激活码框内，您的激活码为:'.$code);
			if($result){
				$res = ['code'=>0,'msg'=>'请去邮箱获取测试码'];
			}else{
				$res = ['code'=>-1,'msg'=>'邮箱配置错误或无服务能力，请排查！'];
			}
		 }
		 return json($res);
	 }
	 
	  public function activeMailServer()
	 {
		 if(Request::isPost()){
			$eCode = Request::param('code');
			$sCode = Cache::get('test_code');
			if($eCode == $sCode){
				$result = Db::name('mail_server')->update(['id'=>1,'active'=>1]);
				if($result){
					$res = ['code'=>0,'msg'=>'邮箱服务激活成功'];
				} else {
					$res = ['code'=>-1,'msg'=>'激活服务出错！'];
				}
			}else{
				$res = ['code'=>-1,'msg'=>'激活码错误！！！'];
			}
		 }
		 return json($res);
	 }
	
	/**配置设置
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

			$setConf = new SetConf;
			$value = [
				'config'=>$conf
			];
			$upRes = $setConf->setConfig('taoler',$value);
			return $upRes;
		}
    }

	//上传logo
	public function upload()
	{
        $uploads = new \app\common\lib\Uploads();
        $upRes = $uploads->put('file','logo',2000,'image','uniqid');
        $logoJson = $upRes->getData();
		if($logoJson['status'] == 0){
			$result = Db::name('system')->where('id', 1)->cache('system')->update(['logo'=>$logoJson['url']]);
			if($result){
				$res = ['code'=>0,'msg'=>'上传logo成功'];
			} else {
				$res = ['code'=>1,'msg'=>'上传错误'];
			}
        }
	return json($res);
	}
		
}
