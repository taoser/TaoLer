<?php
/**
 * Created by PhpStorm.
 * User: TaoLer changlin_zhao@qq.com
 * Date: 2021-03-12
 * Time: 17:24
 */
namespace app\common\lib;


class SetConf
{
	protected string $str = '';
	
	function __construct(string $fileName)
	{
		$this->file = $fileName;
		$this->file = app()->getConfigPath() . $fileName . '.php';
		$this->str = $str = file_get_contents($this->file); //加载配置文件
	}
	
    /**
     * 修改配置
     * @param string $file
     * @param array $data
     * @return \think\response\Json
     */
    function setConfig(string $file,array $data=[])
    {
        if (is_array($data)){
            $fileurl = app()->getConfigPath() . $file.".php";
            $string = file_get_contents($fileurl); //加载配置文件
            foreach ($data as $key => $value) {
				if(is_array($value)){//二维数组
					foreach ($value as $k => $v) {
						if(is_int($v)){//数字类型
							$pats = '/\'' . $k . '\'(.*?),/';
							$reps = "'". $k. "'". "	=> " . $v .",";
							//halt($reps);
						}else{//字符类型
							$pats = '/\'' . $k . '\'(.*?)\',/';
							$reps = "'". $k. "'". "	=> " . "'".$v ."',";
						}

						$string = preg_replace($pats, $reps, $string); // 正则查找然后替换
					}
					
				}else{//一维数组
					
					if(is_int($value)){//数字类型
						$pats = '/\'' . $key . '\'(.*?),/';
						$reps = "'". $key. "'". "   => " . "".$value .",";
					}else{//字符类型
						$pats = '/\'' . $key . '\'(.*?)\',/';
						$reps = "'". $key. "'". "   => " . "'".$value ."',";
					}
					
					$string = preg_replace($pats, $reps, $string); // 正则查找然后替换
				}
            }
			try {
				file_put_contents($fileurl, $string); // 写入配置文件
			}
			catch (\Exception $e) {
				// 这是进行异常捕获
				//$e->getMessage();
				return json(['code'=>-1,'msg'=> $file . '无写入权限']);
			}

            return json(['code'=>0,'msg'=>'配置修改成功']);
        }else{
           return json(['code'=>-1,'msg'=>'配置项错误！']);
        }
    }
	
	
	
	// 修改配置
	function editConfig($data)
    {
		
        if (is_array($data)){
			
            foreach ($data as $key => $value)
			{
				// 多维数组
				if (is_array($value)) {
					$this->editConfig($value);
				} else {
					// 一维数组
					if(is_int($value)){
						// 数字类型
						$pats = '/\'' . $key . '\'(.*?),/';
						$reps = "'". $key. "'". "   => " . "".$value .",";
					} else {
						// 字符类型
						$pats = '/\'' . $key . '\'(.*?)\',/';
						$reps = "'". $key. "'". "   => " . "'".$value ."',";
					}
				
				$this->str = preg_replace($pats, $reps, $this->str); // 正则查找然后替换
				}
            }
			
			try {
				file_put_contents($this->file, $this->str); // 写入配置文件
			}
			catch (\Exception $e) {
				// 这是进行异常捕获
				echo $e->getMessage();
				//return json(['code'=>-1,'msg'=> $file . '无写入权限']);
			}
        }
		return true;
		
    }
	
	/*  删除配置
	*
	*/
	function del(array $arr)
    {
        if (is_array($arr)){
            foreach ($arr as $key => $value)
			{
				// 递归删除key
				if (is_array($value)) {
					$this->del($value);
				} else {
					// 正则查找然后替换
					$pats = '/\s?\'' . $value . '\'(.*?)\r\n/';
					$this->str = preg_replace($pats, '', $this->str);
				}
				
				/* 匹配空数组
				*	'key' => [
				*		],
				*/ 
				$pats = '/\s?\'\w+\'\s*\=\>\s*\[\s*\]{1}\S*\,?\s$/m';
				$this->str = preg_replace($pats, '', $this->str);
				
            }
			//写入配置
			file_put_contents($this->file, $this->str); // 写入配置文件		
        }
		
		return true;
    }
	
}
