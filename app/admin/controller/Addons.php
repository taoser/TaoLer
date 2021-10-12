<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;
use app\admin\model\Addons as AddonsModel;
use taoler\com\Files;
use taoler\com\Api;

class Addons extends AdminController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
		$type = input('type');
		//$filter = input('filter') ? input('filter') : 'public-list';
		//动态field
		switch($type){
			//已安装
			case 'installed':
				$col = [
					['type' => 'numbers', 'fixed'=> 'left'],
					['field' => 'name','title'=> '插件', 'width'=> 150],
					['field'=> 'title','title'=> '标题', 'width'=> 100],
					['field'=> 'version','title'=> '版本', 'width'=> 100],
					['field' => 'author','title'=> '作者', 'width'=> 100],
					['field' => 'description','title'=> '简介', 'minWidth'=> 200],
					['field' => 'status','title'=> '状态', 'width'=> 100],
					['field' => 'install','title'=> '安装', 'width'=> 100],
					['field' => 'ctime','title'=> '到期时间', 'width'=> 150],
					['title' => '操作', 'width'=> 220, 'align'=>'center', 'toolbar'=> '#addons-installed-tool']
				];
				break;
			//在线
			case 'onlineAddons':
				$col = [
					['type' => 'numbers', 'fixed'=> 'left'],
					['field' => 'name','title'=> '插件', 'width'=> 150],
					['field'=> 'title','title'=> '标题', 'width'=> 100],
					['field'=> 'version','title'=> '版本', 'width'=> 100],
					['field' => 'author','title'=> '作者', 'width'=> 100],
					['field' => 'description','title'=> '简介', 'minWidth'=> 200],
					['field' => 'price','title'=> '价格(元)'],
					['field' => 'status','title'=> '状态', 'width'=> 100],
					['field' => 'install','title'=> '安装', 'width'=> 100],
					['field' => 'ctime','title'=> '时间', 'width'=> 150],
					['title' => '操作', 'width'=> 150, 'align'=>'center', 'toolbar'=> '#addons-tool']
				];
				break;
			default:
				$col = [
					['type' => 'numbers', 'fixed'=> 'left'],
					['field' => 'name','title'=> '插件', 'width'=> 150],
					['field'=> 'title','title'=> '标题', 'width'=> 100],
					['field'=> 'version','title'=> '版本', 'width'=> 100],
					['field' => 'author','title'=> '作者', 'width'=> 100],
					['field' => 'description','title'=> '简介', 'minWidth'=> 200],
					['field' => 'status','title'=> '状态', 'width'=> 100],
					['field' => 'install','title'=> '安装', 'width'=> 100],
					['field' => 'ctime','title'=> '到期时间', 'width'=> 150],
					['title' => '操作', 'width'=> 220, 'align'=>'center', 'toolbar'=> '#addons-installed-tool']
				];
		}

		View::assign('col',$col);
		return View::fetch();
    }
	
	 public function addonsList()
    {
       
		$type = input('type') ? input('type') : 'installed';
		$res = [];

			switch($type){
				
				//已安装
				case 'installed':
					$addons = Files::getDirName('../addons/');
					if($addons){
						$res = ['code'=>0,'msg'=>'','count'=>5];
						foreach($addons as $v){
							$info_file = '../addons/'.$v.'/info.ini';
							$info = parse_ini_file($info_file);
							$res['data'][] = $info;
						}
					}

					break;
				//在线	
				case 'onlineAddons':
					$url = $this->getSystem()['api_url'].'/v1/addons';
					$addons = Api::urlPost($url,[]);
					if( $addons->code !== -1){
						$res['code'] = 0;
						$res['msg'] = '';
						$res['data'] = $addons->data;
					}
					
					break;
				//已安装
				default:
					$addons = Files::getDirName('../addons/');
					if($addons){
						$res = ['code'=>0,'msg'=>'','count'=>5];
						foreach($addons as $v){
							$info_file = '../addons/'.$v.'/info.ini';
							$info = parse_ini_file($info_file);
							$res['data'][] = $info;
						}
					}
				break;	
			}
			return json($res);
		
		
    }
	

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function install()
    {
       //
    }




    /**
     * 上传版本的zip资源
     *
     * @param
     * @param  int  $id
     * @return \think\Response
     */
    public function uploadZip()
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
		$version = AddonsModel::find($id);
		$res = $version->delete();
		if($res){
			return json(['code'=>0,'msg'=>'删除成功']);
		} else {
			return json(['code'=>-1,'msg'=>'删除失败']);
		}
    }
}
