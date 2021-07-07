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
use taoler\com\Api;
use taoler\com\Str;
use taoler\com\Files;
use think\facade\Config;
use think\facade\Log;
use app\common\lib\ZipFile;
use app\common\lib\SetConf;
use app\common\lib\SqlFile;

class Upgrade extends AdminController
{
    public $root_dir = "../";	//站点代码的根目录
    public $backup_dir = "../runtime/update/backup_dir/";	//备份目录
    public $upload_dir = "../runtime/update/upload_dir/";	//升级包目录
    public $sys_version_num;	//当前系统的版本
	
	public function __construct()
	{
		$this->sys_version = Config::get('taoler.version');
		$this->pn = Config::get('taoler.appname');
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
		$data = Request::only(['key']);
		if(empty($data['key'])){
			return json(['code'=>0,'msg'=>'请填写正确的key']);
		}
		$res = Db::name('system')->update(['key'=>$data['key'],'id'=>1]);
		if($res){
			$res = ['code'=>0,'msg'=>'保存成功'];
		} else {
			$res = ['code'=>-1,'msg'=>'保存失败'];
		}
		return json($res);
	}
	
	//修改key
	public function keyedit()
	{
		$key = Db::name('system')->field('key,upcheck_url,upgrade_url')->find(1);
		
		if(Request::isAjax()){
			$data = Request::only(['key','upcheck_url','upgrade_url']);
			if(empty($data['key'])){
				return json(['code'=>-1,'msg'=>'请正确填写申请到的key']);
			}
			$res = Db::name('system')->update(['key'=>$data['key'],'upcheck_url'=>$data['upcheck_url'],'upgrade_url'=>$data['upgrade_url'],'id'=>1]);
			if($res){
				$res = ['code'=>0,'msg'=>'修改成功'];
			} else {
				$res = ['code'=>-1,'msg'=>'修改失败'];
			}
			return json($res);
		}
		View::assign('key',$key);
		return View::fetch();
	}
	
	//升级前的版本检测
	public function check()
	{
		$url = $this->sys['upcheck_url'].'?pn='.$this->pn.'&ver='.$this->sys_version;
		$versions = Api::urlGet($url);

		//判断服务器状态
		$version_code = $versions->code;
		if($version_code == -1){
			$res = json(['code'=>$version_code,'msg'=>$versions->msg]);
		}
		if($version_code == 1){
            $res = json(['code'=>$versions->code,'msg'=>$versions->msg,'version'=>$versions->version,'upnum'=>$versions->up_num]);
		}
		if($version_code == 0){
            $res = json(['code'=>$versions->code,'msg'=>$versions->msg]);
		}

        return $res;
	}

    /**
     * 备份
     * @param string $dir
     * @param string $backDir
     * @param array $ex
     * @return \think\response\Json
     */
	public function backFile(string $dir,string $backDir,array $ex)
    {
        $backRes = Files::copydirs($dir, $backDir, $ex);
        $backData = $backRes->getData();
        if($backData['code'] == -1){
            Log::channel('update')->info('update:{type} {progress} {msg}',['type'=>'error','progress'=>'25%','msg'=>'备份失败!']);
            return json(['code'=>-1,'msg'=>$backRes['msg']]);
        }
        Log::channel('update')->info('update:{type} {progress} {msg}',['type'=>'success','progress'=>'30%','msg'=>'执行文件备份成功！']);
    }
	
    /**
     * 在线更新
     */
    public function upload()
    {
		$url = $this->sys['upgrade_url'].'?url='.$this->sys['domain'].'&key='.$this->sys['key'].'&pn='.$this->pn.'&ver='.$this->sys_version;
		$versions = Api::urlGet($url);
        Log::channel('update')->info('update:{type} {progress} {msg}',['type'=>'check','progress'=>'0%','msg'=>'===>升级检测开始===>']);
		//判断服务器状态
		$version_code = $versions->code;
		if($version_code == -1){
            Log::channel('update')->info('update:{type} {progress} {msg}',['type'=>'check eroor','progress'=>'5%','msg'=>'===>服务器链接失败===>']);
			return json(['code'=>$version_code,'msg'=>$versions->msg]);
		}

		$version_num = $versions->version;
		$file_url = $versions->src;
   
		//判断远程文件是否可用存在
		$header = get_headers($file_url, true);
		if(!isset($header[0]) && (strpos($header[0], '200') || strpos($header[0], '304'))){
			return json(['code'=>-1,'msg'=>'获取远程文件失败']);
		}

        //把远程文件放入本地

        //拼接路径
        //$upload_dir = substr($this->upload_dir,-1) == '/' ? $this->upload_dir : $this->upload_dir.'/';
        $upload_dir = Files::getDirPath($this->upload_dir);
        Files::mkdirs($upload_dir);

		$package_file = $upload_dir.'taoler_'.$version_num.'.zip';  //升级的压缩包文件
		$cpfile = copy($file_url,$package_file);

        if(!$cpfile)
        {
            return json(['code'=>-1,'msg'=>'下载升级文件失败']);  
        }
        //记录下日志
        Log::channel('update')->info('update:{type} {progress} {msg}',['type'=>'success','progress'=>'20%','msg'=>'上传升级包'.$version_num.'成功！']);

        //升级前备份代码
		$ex = array('.git','.idea','runtime','data','addons','config','extend','mysql','public','vendor','view');  //  排除备份文件夹
        $this->backFile($this->root_dir,$this->backup_dir,$ex);

        //执行升级
        $updateRes = $this->execute_update($package_file);
		$upDate = $updateRes->getData();
		if($upDate['code'] == -1){
			return json(['code'=>-1,'msg'=>$upDate['msg']]);
		}
		
		//更新版本
		//Db::name('system')->update(['sys_version_num'=>$version_num,'id'=>1]);
        $setConf = new SetConf;
        $value = [
            'version'    => $version_num
        ];

		$upRes = $setConf->setConfig('taoler',$value);
		$result = $upRes->getData();
		if($result['code'] == -1){
			return json(['code'=>-1,'msg'=>$result['msg']]);
		}
		return json(['code'=>0,'msg'=>'升级成功']);
		
    }

    /**升级执行
     * @param string $package_file
     * @return \think\response\Json
     */
    private function execute_update(string $package_file)
    {
        //解压 zip文件有密码的话需要解密
        $uzip = new ZipFile();
        $zipDir = strstr($package_file, '.zip',true);   //返回文件名后缀前的字符串
        $zipPath = Files::getDirPath($zipDir);  //转换为带/的路径 压缩文件解压到的路径
        $unzip_res = $uzip->unzip($package_file,$zipPath,true);

        if(!$unzip_res)
        {
            return json(['code'=>-1,'msg'=>'解压失败']);
        }
        //解压成功，得到文件夹
        //$package_name = str_replace('.zip','',$package_file);

        Log::channel('update')->info('update:{type} {progress} {msg}',['type'=>'success','progress'=>'50%','msg'=>'升级文件解压成功！']);

		//升级sql操作
        $upSql = $zipPath.'runtime/mysql_update.sql';
		if(file_exists($upSql))
		{
			$sqlRes = $this->db_update($upSql);
			$upDate = $sqlRes->getData();
			if($upDate['code'] == -1){
				return json(['code'=>-1,'msg'=>$upDate['msg']]);
			}
			//删除sql语句
			unlink($upSql);
		}
		
		//升级PHP
        if(is_dir($zipPath))
        {
			//升级前的写入文件权限检查
			$allUpdateFiles = Files::getAllFile($zipPath);
			
			if (empty($allUpdateFiles)) return json(['code' => -1, 'msg' => '无可更新文件。']);
			$checkString    = '';
			foreach ($allUpdateFiles as $updateFile) {
				$coverFile  = ltrim(str_replace($zipPath, '', $updateFile), DIRECTORY_SEPARATOR);
				$dirPath    = dirname('../'.$coverFile);
				if (file_exists('../'.$coverFile)) {
					if (!is_writable('../'.$coverFile)) $checkString .= $coverFile . '&nbsp;[<span class="text-red">' . '无写入权限' . '</span>]<br>';
				} else {
					if (!is_dir($dirPath)) @mkdir($dirPath, 0777, true);
					if (!is_writable($dirPath)) $checkString .= $dirPath . '&nbsp;[<span class="text-red">' . '无写入权限' . '</span>]<br>';
				}
			}

			if (!empty($checkString)) return json(['code' => -1, 'msg' => $checkString]);

            $cpRes = Files::copyDirs($zipPath,$this->root_dir);
			$cpData = $cpRes->getData();
            //更新失败
            if($cpData['code'] == -1)
            {
                //数据库回滚
/*
                if(file_exists($this->upload_dir.'/'.$package_file.'/mysql/mysql_rockback.sql'))
                {
                    $this->database_operation($this->upload_dir.'/'.$package_file.'/mysql/mysql_rockback.sql');
                }
*/
                //php代码回滚 升级前备份的代码
                Files::copydirs($this->backup_dir, $this->root_dir);
                
				return json(['code'=>-1,'msg'=>$cpData['msg']]);
            }
			
			Log::channel('update')->info('update:{type} {progress} {msg}',['type'=>'success','progress'=>'70%','msg'=>'升级文件执行成功！']);
			//清除
			Files::delDirAndFile($this->upload_dir);
			Files::delDirAndFile($this->backup_dir);
        }

        Log::channel('update')->info('update:{type} {progress} {msg}',['type'=>'success','progress'=>'100%','msg'=>'升级成功！']);
        //更新系统的版本号了
        //更新php的版本号了(应该跟svn／git的版本号一致)
        //更新数据库的版本号了(应该跟svn／git的版本号一致)

        return json(['code'=>0,'msg'=>'升级文件执行成功']);
    }

	/**
     * 处理升级包上传
     */
    public function uploadZip()
    {
		$files = request()->file('file');
		$name = $files->getOriginalName();

        //校验后缀
        $ext = pathinfo($name,PATHINFO_EXTENSION);  //文件后缀
        if($ext != 'zip')
        {
            return json(['code'=>0,'msg'=>'请上传文件格式不对']);
        }
        //对比版本号
        $fname = pathinfo($name,PATHINFO_FILENAME);   //无后缀文件名
        $version_num = array_pop(explode('_',$fname));

        $verRes = version_compare($version_num,$this->sys_version,'>');
        if(!$verRes){
            return json(['code'=>-1,'msg'=>'不能降级，请选择正确版本']);
        }

		$upDir = $this->upload_dir.$fname;
		//$mv = $files->move('/../tmp/web/upload_dir',$version_num);
		$mfile = move_uploaded_file($files,$upDir);
        if(!$mfile)
        {
            return json(['code'=>0,'msg'=>'上传文件失败']);  
        }

        //升级前备份代码
        $ex = array('.git','.idea','runtime','data','addons','config','extend');  //  排除备份文件夹
        $this->backFile($this->root_dir,$this->backup_dir,$ex);
		
        //执行升级
        $upres = $this->execute_update($mfile);

        //更新版本
        //Db::name('system')->update(['sys_version_num'=>$version_num,'id'=>1]);
        $setConf = new SetConf;
        $value = [
            'version'    => $version_num
        ];

        $upRes = $setConf->setConfig('taoler',$value);
        $result = $upRes->getData();
        if($result['code'] == -1){
            return json(['code'=>-1,'msg'=>$result['msg']]);
        }
        return json(['code'=>0,'msg'=>'升级成功']);
    }


    /**
     * 数据库操作
     */
    public function database_operation($file)
    {
        $mysqli = new mysqli('localhost','root','root','test');
        if($mysqli->connect_errno)
        {
            return json(['code'=>0,'msg'=>'Connect failed:'.$mysqli->connect_error]);
        }
        $sql = file_get_contents($file);
        $a = $mysqli->multi_query($sql);
        return ['code'=>1,'msg'=>'数据库操作OK'];
    }
	
	/**
     * 执行数据库操作
     */
    public function db_update($file)
    {
		$sqlf = new SqlFile();
		$sql_array = $sqlf->load_sql_file($file);
		foreach($sql_array as $v){
			$sqlRes = Db::execute($v);
			if ($sqlRes === false) {
		        return json(['code'=>-1,'msg'=>'数据库升级失败']);
		    }
		}
        return json(['code'=>0,'msg'=>'数据库升级成功']);
    }


}