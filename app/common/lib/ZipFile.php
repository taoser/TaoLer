<?php
namespace app\common\lib;


class ZipFile
{
    /**
     * header('Content-type:text/html;charset=utf-8');
     * @param string $filename  解压文件
     * @param string $dir   解压到路径
     * @param bool $overwrite 是否覆盖文件 true false
     * @return bool
     */
	public function unZip($filename, $dir ,$overwrite=true)
	{
		if(!is_dir($dir)) {
			//mkdir($dir, 0777, true);//创建目录保存解压内容
			$this->create_dirs($dir);
		}
		
		if(file_exists($filename)) {
			$resource = zip_open($filename);
			
			while($zip = zip_read($resource)) {
				if(zip_entry_open($resource, $zip)) {
					
					//获得文件名，mac压缩成zip，解压需要过滤资源库隐藏文件
					$file_content = zip_entry_name($zip);
					
					 
					 // 如果文件不在根目录中
					$pos_last_slash = strrpos($file_content, "/"); 					 
					$file_name = substr($file_content,  $pos_last_slash+1);
					
					if(empty($file_name)){
						
						$pt = $this->create_dirs($dir.$file_content);
					}
					
					if($file_name) {
						
						$save_path = $dir.$file_content;
						
						if(file_exists($save_path)) {
							if($overwrite === true){
								//echo $file_name . '<pre />';	
								$file_size = zip_entry_filesize($zip);
								$file = zip_entry_read($zip, $file_size);
								$fpc = file_put_contents($save_path, $file);
								//zip_entry_close($zip);	
							}else{
								//echo '文件夹内已存在文件 "' . $file_name . '" <pre />';
								return json(['code'=>-1,'msg'=>'文件夹内已存在文件']);
							}
							
						}else {
							//echo $file_name . '<pre />';	
							$file_size = zip_entry_filesize($zip);
							$file = zip_entry_read($zip, $file_size);
							$fpc = file_put_contents($save_path, $file);
							//zip_entry_close($zip);
						}
					
					}
				zip_entry_close($zip);	
				}
			}
			zip_close($resource);
			
		}else{
			return false;
		}
		return true;
	}

    /**
     * 创建文件夹及子文件夹
     * @param $path
     * @return bool
     */
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