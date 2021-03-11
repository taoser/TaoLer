<?php
/**
 *  升级包规定的目录结构
 *  xxx_版本号.zip(如：xxx_1.0.0.zip)
 *   |
 *   |————mysql
 *   |    |
 *   |    |___mysql_update.sql(更新脚本) //create table test(id init(11)); create table test2(id init(11));
 *
 *   |    |___mysql_rockback.sql（回滚脚本） //drop table test; //drop table test2;
 *   |    
 *   |____php
 * 
 */
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\exception\ValidateException;
use app\admin\controller\Uzip;
use think\facade\Cache;
use taoler\com\Api;
use taoler\com\Str;
use think\facade\Config;

class Upgrade extends AdminController
{
    public $update_log = "../runtime/update/update_log.log";	//系统升级日志
    public $return_log = "../runtime/update/return_log.log";	//系统回滚日志
    public $progress_log = "../runtime/update/progress_log.log";	//记录进度
    public $root_dir = "../";	//站点代码的根目录
    public $backup_dir = "../runtime/update/backup_dir/";	//备份目录
    public $upload_dir = "../runtime/update/upload_dir/";	//升级包目录
    public $sys_version_num;	//当前系统的版本
	
	public function __construct()
	{
		$this->sys_version = Config::get('taoler.version');
		$this->sys = Db::name('system')->where('id',1)->find();
	}
    
	
	/** 升级界面 */
    public function index()
    {	//字符隐藏
		$key = Str::func_substr_replace($this->sys['key']);
		$sys_base = [
			'key' => $key,
			'upcheck_url' => $this->sys['upcheck_url'],
			'upgrade_url' => $this->sys['upgrade_url'],
		];
		View::assign('ver_num',$sys_base);
       return View::fetch(); 
    }

	//设置key
	public function key()
	{
		$data = Request::param();
		if($data['key']== ''){
			return json(['code'=>0,'msg'=>'请填写正确的key']);
		}
		$res = Db::name('system')->update(['key'=>$data['key'],'id'=>1]);
		if($res){
			$res = ['code'=>1,'msg'=>'保存成功'];
		} else {
			$res = ['code'=>0,'msg'=>'保存失败'];
		}
		return json($res);
	}
	
	//修改key
	public function keyedit()
	{
		$key = Db::name('system')->field('key,upcheck_url,upgrade_url')->find(1);
		
		if(Request::isAjax()){
			$data = Request::param();
			if($data['key']== ''){
				return json(['code'=>0,'msg'=>'请正确填写申请到的key']);
			}
			$res = Db::name('system')->update(['key'=>$data['key'],'upcheck_url'=>$data['upcheck_url'],'upgrade_url'=>$data['upgrade_url'],'id'=>1]);
			if($res){
				$res = ['code'=>1,'msg'=>'修改成功'];
			} else {
				$res = ['code'=>0,'msg'=>'修改失败'];
			}
			return json($res);
		}
		View::assign('key',$key);
		return View::fetch();
	}
	
	//升级前的版本检测
	public function check($url)
	{
		$url = $url.'?ver='.$this->sys_version;
		//$versions = json_decode(Api::urlGet($url));
		$versions = Api::urlGet($url);
		//var_dump($versions);
		
		//判断服务器状态
		$version_code = $versions->code;
		if($version_code == -1){
			return json(['code'=>$version_code,'msg'=>$versions->msg]);
		}
		
		if($version_code == 1){
			return json(['code'=>$versions->code,'msg'=>$versions->msg,'version'=>$versions->version,'upnum'=>$versions->up_num]);
		}
		
		if($version_code == 0){
			return json(['code'=>$versions->code,'msg'=>$versions->msg]);
		}
		
		//版本比较
/*		
		$version_num = $versions->version;	//最新版本
		$up_num =$versions->up_num;	//可更新版本数
		$res = version_compare($version_num,$this->sys_version_num,'>');
		if($res){
			return json(['code'=>1,'msg'=>'发现新版本','version'=>$version_num,'upnum'=>$up_num]);
		} else {
			return json(['code'=>0,'msg'=>'暂时还没更新哦！ ==8']);
		}
*/		
	}
	
	
    /**
     * 在线更新
     */
    public function upload()
    {
		$data = Request::only(['url','key']);
		
		if(empty($data['key'])){
			return json(["code"=>0,"msg"=>"请配置正确升级key"]);
		}
		$url = $data['url'].'?url='.$this->sys['domain'].'&key='.$data['key'].'&ver='.$this->sys_version;
//var_dump($url);
		$versions = Api::urlGet($url);
	
		//判断服务器状态
		$version_code = $versions->code;
		if($version_code == -1){
			return json(['code'=>$version_code,'msg'=>$versions->msg]);
		}
		
		$version_num = $versions->version;
		$file_url = $versions->src;
		
		//判断远程文件是否可用存在
		$header = get_headers($file_url, true);
		if(!isset($header[0]) && (strpos($header[0], '200') || strpos($header[0], '304'))){
			return json(["code"=>-1,"msg"=>"获取远程文件失败"]);
		}
		
		 if(!is_dir($this->upload_dir)){
			$this->create_dirs($this->upload_dir);
		}
		//把远程文件放入本地
		$package_file = $this->upload_dir.'tao_'.$version_num.'.zip';
		$cpfile = copy($file_url,$package_file);
        if(!$cpfile)
        {
            return json(["code"=>-1,"msg"=>"下载升级文件失败"]);  
        } 

        //记录下日志
        $this->save_log("上传升级包成功！");
        $this->update_progress("20%");

		//升级前备份代码
		$ex = array('app','view');
		$backup_code_res = $this->copydir('../', $this->backup_dir, $ex);
		if(!$backup_code_res){
			$this->save_log("备份失败！");
			return json(["code"=>0,"msg"=>"备份失败"]);
		}
        $this->update_progress("30%");
		
        //执行升级
        $upres = $this->execute_update($package_file);
		//更新版本
		Db::name('system')->update(['sys_version_num'=>$version_num,'id'=>1]);
		//$res = Config::set(['version' => $version_num,'salt' => 'taoler'], 'taoler');
		//var_dump($res);
		if($upres){
			return json(["code"=>0,"msg"=>"升级成功"]);
		}else {
			return json(["code"=>-1,"msg"=>"升级失败"]);
		}
    }
	
	/**
     * 处理升级包上传
     */
    public function uploadZip()
    {
		$files = request()->file('file');

        if($files)
        {
			$name = $files->getOriginalName();

            if(!$name)
            {
                return json(["code"=>0,"msg"=>"请上传升级包文件"]);
            }
        }
        //校验后缀
        $astr = explode('.',$name);
        $ext = array_pop($astr);
        if($ext != 'zip')
        {
            return json(["code"=>0,"msg"=>"请上传文件格式不对"]);
            
        }
		
        //对比版本号
        $astr = explode('_',$name);
        $version_num = str_replace(".zip", '',array_pop($astr));
		//var_dump($version_num);
        if(!$version_num)
        {
            return json(["code"=>0,"msg"=>"获取版本号失败"]);
            
        }
        //对比
        if(!$this->compare_version($version_num))
        {
            return json(["code"=>0,"msg"=>"版本升级不能降级！请检查..."]);  
        }
		
		if(!is_dir($this->upload_dir)){
			$this->create_dirs($this->upload_dir);
		}
		
		$package_file = $this->upload_dir.$name;
		//$mv = $files->move('/../tmp/web/upload_dir',$version_num);
		$mfile = move_uploaded_file($files,$package_file);
        if(!$mfile)
        {
            return json(["code"=>0,"msg"=>"上传文件失败"]);  
        } 

        //记录下日志
        $this->save_log("上传升级包成功！");
        $this->update_progress("20%");

		//升级前备份代码
		$ex = array('app','view');
		$backup_code_res = $this->copydir('../', $this->backup_dir, $ex);
		if(!$backup_code_res){
			$this->save_log("备份失败！");
			return json(["code"=>0,"msg"=>"备份失败"]);
		}
        $this->update_progress("30%");
		
        //执行升级
        $upres = $this->execute_update($package_file);
		
		//更新版本
		Db::name('system')->update(['sys_version_num'=>$version_num,'id'=>1]);
		//Config::set(['version' => $version_num], 'taoler');
		if($upres){
			return json(["code"=>1,"msg"=>"升级成功"]);
		}else {
			return json(["code"=>0,"msg"=>"升级失败"]);
		}
    }

    /**
     * 升级操作
     * @return [type] [description]
     */
    private function execute_update($package_file)
    {
        //解压 如何使用zip加密压缩，这里解压缩的时候注意要解密
		$uzip = new Uzip();
		$unzip_res = $uzip->uzip($package_file,$this->upload_dir,true);
        if(!$unzip_res)
        {
            $this->save_log("解压失败");
            return json(["code"=>0,"msg"=>"解压失败"]);
        }
		//解压成功，得到文件夹
		$package_name = str_replace(".zip","",$package_file);
//var_dump($package_name);		
        $this->update_progress("50%");
		
/*
        //升级mysql
        if(file_exists($this->upload_dir.'/'.$package_file."/mysql/mysql_update.sql"))
        {
            $result = $this->database_operation($this->upload_dir.'/'.$package_file."/mysql/mysql_update.sql");
            if(!$result['code'])
            {
                echo json($result);die;
            }
        }
*/		
		
        $this->update_progress("70%");
		

        if(is_dir($package_name.'/'))
        {
            
			//升级PHP
			$cp_res = $this->copydir($package_name.'/', $this->root_dir);
            if(!$cp_res)
            {
                $this->save_log("php更新失败");
                //数据库回滚
                if(file_exists($this->upload_dir.'/'.$package_file."/mysql/mysql_rockback.sql"))
                {
                    $this->save_log("数据库回滚");
                    $this->database_operation($this->upload_dir.'/'.$package_file."/mysql/mysql_rockback.sql");
                 
                }
				
                //php代码回滚 升级前备份的代码
                
				$backup_code_res = $this->copydir($this->backup_dir, $this->zip_dir);
				if($backup_code_res){
                $this->save_log("php回滚");
                return json(["code"=>0,"msg"=>"php更新失败"]);
                }
            }
        }
	
        //把解压的升级包清除
		//$del_zip = unlink($package_file);
		$this->delDirAndFile($this->upload_dir);
		$this->delDirAndFile($this->backup_dir);
        
        $this->update_progress("100%");
        //更新系统的版本号了
       //更新php的版本号了(应该跟svn／git的版本号一致)
       //更新数据库的版本号了(应该跟svn／git的版本号一致)
	 
        return true;  
    }



    /**
     * 比较代码版本
     * @return [type] [description]
     */
    private function compare_version($version_num='1.0.0')
    {
        
        return version_compare($version_num,$this->sys_version_num,'>');
		//return json(['code'=>1,'msg'=>'版本','data'=>$version]);
    }


    /**
     * 数据库操作
     */
    public function database_operation($file)
    {
        $mysqli = new mysqli("localhost","root","root","test");
        if($mysqli->connect_errno)
        {
            return json(["code"=>0,"msg"=>"Connect failed:".$mysqli->connect_error]);
        }
        $sql = file_get_contents($file);
        $a = $mysqli->multi_query($sql);
        return ["code"=>1,"msg"=>"数据库操作OK"];
    }

    /**
     * 返回系统升级的进度
     */
    public function update_progress($progress)
    {
        exec(" echo '".$progress."' > $this->progress_log ");
		
    }
    /**
     * 记录日志
     */
    public function save_log($msg,$action="update")
    {
        $msg .= date("Y-m-d H:i:s").":".$msg."\n";
        if($action == "update")
        {
            exec(" echo '".$msg."' >>  $this->update_log ");
        }else
        {
            exec(" echo '".$msg."' >>  $this->return_log ");
        }
    }
	
	
	/**
	 * 复制文件夹$source下的文件和子文件夹下的内容到$dest下 升级+备份代码
	 * @param $source
	 * @param $dest
	 * @param $ex 定义指定复制的目录,默认全复制
	 */
	public function copydir($source, $dest, $ex=array())
	{
		if (!file_exists($dest)) mkdir($dest);
		if($handle = opendir($source)){
			while (($file = readdir($handle)) !== false) {

				if (( $file != '.' ) && ( $file != '..' )) {
					if ( is_dir($source . $file) ) {
						
						if(count($ex) != 0){
							if(in_array($file,$ex)){
								
								$this->copydir($source . $file.'/', $dest . $file.'/');
							}
						}else{
							$this->copydir($source . $file.'/', $dest . $file.'/');
						}
					} else {
						copy($source. $file, $dest . $file);
					}
				}
			}
			closedir($handle);
			
		} else {
			return false;
		}
		return true;	
	}
	
	/**
	 * 删除文件夹及内容 
	 * @param $dirPath
	 * @param $nowDir 是否删除当前文件夹$dirPath true false
	 */
	public function delDirAndFile( $dirPath, $nowDir=false ) 

	{ 
		if ( $handle = opendir($dirPath) ) { 

			while ( false !== ( $item = readdir( $handle ) ) ) { 

				if ( $item != '.' && $item != '..' ) { 
					
					$path = $dirPath.$item;
					//var_dump($path);
					if (is_dir($path)) { 
						$this->delDirAndFile($path.'/'); 
						rmdir($path.'/');
					} else { 
						unlink($path); 
					} 
				} 
			} 

		closedir( $handle ); 
/*
			if($nowDir == true){
				if(!rmdir($dirPath)){
					return false;
				}
			}
*/
		} else {
			return false;
		}
		return true;
	} 
	
	//创建多文件夹
	public function create_dirs($path)
	{
	  if (!is_dir($path))
	  {
		$directory_path = "";
		$directories = explode("/",$path);
		array_pop($directories);
	   
		foreach($directories as $directory)
		{
		  $directory_path .= $directory."/";
		  if (!is_dir($directory_path))
		  {
			mkdir($directory_path);
			chmod($directory_path, 0777);
		  }
		}
	  }
	}


}