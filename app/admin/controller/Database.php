<?php

namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\Request;
use think\facade\View;
use think\facade\Config;
use phpspirit\databackup\BackupFactory;
use taoler\com\Files;

class Database extends AdminController
{
	
	public function __construct()
	{
		$this->hostname = Config::get('database.connections.mysql.hostname');
		$this->hostport = Config::get('database.connections.mysql.hostport');
		$this->database = Config::get('database.connections.mysql.database');
		$this->username = Config::get('database.connections.mysql.username');
		$this->password = Config::get('database.connections.mysql.password');
		$this->backdir = Config::get('taoler.databasebackdir');
	}
	
	public function index()
	{
		if(Request::isAjax()){
			$backName = Files::getDirName($this->backdir);

			if(empty($backName)){
                return json(['code'=>-1,'msg'=>'还没有数据']);
            }
			
			$res['count'] = count($backName);
			if($res['count']){
				$res['code'] = 0;
				$res['msg'] = '';
				$res['data'] = [];
				foreach($backName as $k=>$v){
					$res['data'][] = ['id' => $k,
                        'time' 		=> $v,
                        'name' 	=> $v,
                    ];
				}

			}
			return json($res);
		}
		
		return View::fetch();
	}
	
	public function backup()
	{
		//自行判断文件夹
		if (isset($_POST['backdir']) && $_POST['backdir'] != '') {
			$backupdir = $_POST['backdir'];
		} else {
			$backupdir = $this->backdir . date('Ymdhis');
		}

		if (!is_dir($backupdir)) {
			mkdir($backupdir, 0777, true);
		}

		$backup = BackupFactory::instance('mysql', "$this->hostname:$this->hostport", $this->database, $this->username, $this->password);
		$result = $backup->setbackdir($backupdir)
			->setvolsize(0.2)
			->setonlystructure(false) //设置是否只备份目录结构
			->settablelist() //设置要备份的表， 默认全部表 
			->setstructuretable()
			->ajaxbackup($_POST);

		echo json_encode($result);
	}
	
	
	//下载
	public function down()
	{
		$id = input('id');
		var_dump($id);
	}
	
	//删除备份文件夹
	public function delete()
	{
		$name = input('name');
		//var_dump($name);
		$dir = $this->backdir . $name;
		
		$res = Files::delDir($dir);
		
		if($res){
			 return json(['code'=>0,'msg'=>'删除成功']);
		} else {
			 return json(['code'=>-1,'msg'=>'删除失败']);
		}
	}
	
}
