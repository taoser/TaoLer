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
            //var_dump( $fileurl);
            $string = file_get_contents($fileurl); //加载配置文件
            foreach ($data as $key => $value) {
                $pats = '/\'' . $key . '\'(.*?)\',/';
                $reps = "'". $key. "'". "   => " . "'".$value ."',";
                $string = preg_replace($pats, $reps, $string); // 正则查找然后替换
            }
			
			try {
				file_put_contents($fileurl, $string); // 写入配置文件
			}
			catch (\Exception $e) {
				// 这是进行异常捕获
				//$e->getMessage();
				return json(['code'=>-1,'msg'=>$fileurl . '无写入权限']);
			}

            return json(['code'=>0,'msg'=>'配置修改成功']);
        }else{
           return json(['code'=>-1,'msg'=>'配置项错误！']);
        }
    }
}
