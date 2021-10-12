<?php

namespace taoler\com;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
class Files
{
    /**
     * 转换为/结尾的路径
     * @param $path string 文件夹路径
     * @return string
     */
    public static function getDirPath($path)
    {
        return substr($path,-1) == '/' ? $path : $path.'/';
    }

    /**
     * 获取目录下子目录名
     * @param $path string 目录
     * @return array
     */
	public static function getDirName($path)
	{
		if (is_dir($path)) {
			$arr = array();
			$data = scandir($path);
			foreach ($data as $value){
				if($value !='.' && $value != '..' && !stripos($value,".")){
				  $arr[] = strtolower($value);
				}
			  }
			 //return array_merge(array_diff($arr, array('install')));
			 return $arr;
		}
	}
	
	/**
     * 获取目录下文件
     * @param $path string 目录
     * @return array
     */
	public static function getDirFiles($path,$type)
	{
		if (is_dir($path)) {
			$arr = array();
			$data = scandir($path);
			foreach ($data as $value){
				if($value !='.' && $value != '..'){
					if($type == 1){//获取文件夹下文件名 index.html
						$arr[] = $value;
					}else{//获取无后缀名称 index
						$arr[] = basename($value,".html");
					}
				}
			  }
			 return $arr;
		}
	}
	
	/**
     * 列出目录下的所有文件，包括子目录文件,不包含sql目录
     * @param $dirName
     * @return array
     */
    public static function getAllFile($dirName)
    {
        //$dirName    = str_replace('..', '', rtrim($dirName, '/\\'));
        $fileArray  = [];
        if (is_dir($dirName)) {
            $dh = scandir($dirName);
            foreach ($dh as $file) {
                if (!in_array($file, ['.', '..', 'runtime', '.DS_Store'])) {
                    $path = $dirName . DIRECTORY_SEPARATOR . $file;
                    if (!is_dir($path)) $fileArray[] = $path;
                    $fileArray = array_merge($fileArray, self::getAllFile($path));
                }
            }
        }
        return $fileArray;
    }

    /**
     * 创建文件夹及子文件夹
     * @param $path
     * @return bool
     */
	public static function create_dirs($path)
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
		return true;
	  }else {
          return false;
      }

	}

    /**
     * 删除文件夹及内容
     * @param string $dirPath
     * @param bool $nowDir 是否删除当前文件夹目录 true false
     * @return bool
     */
	public static function delDirAndFile(string $dirPath, $nowDir=false )
	{
		if(!is_dir($dirPath)) return 'dir not exist';
		if ( $handle = opendir($dirPath) ) { 

			while ( false !== ( $item = readdir( $handle ) ) ) { 
				if ( $item != '.' && $item != '..' ) { 
					$path = $dirPath.$item;
					if (is_dir($path)) { 
						self::delDirAndFile($path.'/'); 
						rmdir($path.'/');
					} else { 
						unlink($path); 
					} 
				} 
			} 
			closedir( $handle );
            //删除当前文件夹
			if($nowDir == true){
				if(rmdir($dirPath)){
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
		return true;
	}

    /**
     * 复制文件夹文件和子文目录文件，可排除目录复制 升级+备份代码
     * @param string $source 源目录
     * @param string $dest  目标目录
     * @param array $ex 排除目录
     * @return \think\response\Json
     */
	public static function copyDirs(string $source,string $dest, array $ex=array())
	{
		if (is_dir($source)){
			if (!is_dir($dest)) {
            	self::mkdirs($dest, 0777, true);
        	}
			if($handle = opendir($source)){
				while (($file = readdir($handle)) !== false) {
					if (( $file != '.' ) && ( $file != '..' )) {
						if (is_dir($source . $file) ) {
							//拷贝排除的文件夹
                            if(!in_array($file,$ex)){
                                self::copyDirs($source . $file.'/', $dest . $file.'/');
                            }
						} else {
							//copy($source. $file, $dest . $file);
							
						    //拷贝文件
							try{
								copy($source. $file, $dest . $file);
							}
							catch (\Exception $e) {
								// 这是进行异常捕获
								return json(['code'=>-1,'msg'=>$dest . $file . '无写入权限']);
							}
						}
					}
				}
				closedir($handle);
			}
		}
		return json(['code'=>0,'msg'=>'复制完成']);	
	}

    /**
     * 检测目录并循环创建目录
     * @param $dir
     * @return bool
     */
    public static function mkdirs($dir)
    {
        if (!file_exists($dir)) {
            self::mkdirs(dirname($dir));
            mkdir($dir, 0777);
        }
        return true;
    }

    /**
     * 删除文件以及目录
     * @param $dir
     * @return bool
     */
    public static function delDir($dir) {
        //先删除目录下的文件：
//        var_dump(is_dir($dir));
//        if(!is_dir($dir)){
//            return true;
//        }
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    self::delDir($fullpath);
                }
            }
        }
        closedir($dh);
        //删除当前文件夹
        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 复制文件到指定文件
     * @param $source
     * @param $dest
     * @return bool
     */
    public static function copyDir($source, $dest)
    {
        if (!is_dir($dest)) {
            self::mkdirs($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    self::mkdirs($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
        return true;
    }

    /**
     * 写入
     * @param $content
     * @param $filepath
     * @param $type $type 1 为生成控制器 2 模型
     * @throws \Exception
     */
    public static function filePutContents($content,$filepath,$type){
        if($type==1){
            $str = file_get_contents($filepath);
            $parten = '/\s\/\*+start\*+\/(.*)\/\*+end\*+\//iUs';
            preg_match_all($parten,$str,$all);
            $ext_content = '';
            if($all[0]){
                foreach($all[0] as $key=>$val){
                    $ext_content .= $val."\n\n";
                }
            }
            $content .= $ext_content."\n\n";
            $content .="}\n\n";
        }
        ob_start();
        echo $content;
        $_cache=ob_get_contents();
        ob_end_clean();
        if($_cache){
            $File = new \think\template\driver\File();
            $File->write($filepath, $_cache);
        }
    }

    /**
     * 获取文件夹大小
     * @param $dir 根文件夹路径
     * @return bool|int
     */
    public static function getDirSize($dir)
    {
        if(!is_dir($dir)){
            return false;
        }
        $handle = opendir($dir);
        $sizeResult = 0;
        while (false !== ($FolderOrFile = readdir($handle))) {
            if ($FolderOrFile != "." && $FolderOrFile != "..") {
                if (is_dir("$dir/$FolderOrFile")) {
                    $sizeResult += self::getDirSize("$dir/$FolderOrFile");
                } else {
                    $sizeResult += filesize("$dir/$FolderOrFile");
                }
            }
        }

        closedir($handle);
        return $sizeResult;
    }

    /**
     * 创建文件
     * @param $file
     * @param $content
     * @return bool
     */
    public static function createFile($file,$content)
    {

        $myfile = fopen($file, "w") or die("Unable to open file!");
        fwrite($myfile, $content);
        fclose($myfile);
        return true;
    }

    /**
     * 基于数组创建目录
     * @param $files
     */
    public static function createDirOrFiles($files)
    {
        foreach ($files as $key => $value) {
            if (substr($value, -1) == '/') {
                mkdir($value);
            } else {
                file_put_contents($value, '');
            }
        }
    }


    /**
     * 判断文件或目录是否有写的权限
     * @param $file
     * @return bool
     */
    public static function isWritable($file)
    {
        if (DIRECTORY_SEPARATOR == '/' AND @ ini_get("safe_mode") == FALSE) {
            return is_writable($file);
        }
        if (!is_file($file) OR ($fp = @fopen($file, "r+")) === FALSE) {
            return FALSE;
        }
        fclose($fp);
        return TRUE;
    }

    /**
     * 写入日志
     * @param $path
     * @param $content
     * @return bool|int
     */
    public static function writeLog($path, $content)
    {
        self::mkdirs(dirname($path));
        return file_put_contents($path, "\r\n" . $content, FILE_APPEND);
    }
}