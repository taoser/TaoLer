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
}
