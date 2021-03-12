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
     * 修改配置文件
     * @param  string $file 配置文件名(不需要后辍)
     * @param  array  $data 需要更新或添加的配置
     * @return bool
     */
    function setconfig($file,$data)
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
            file_put_contents($fileurl, $string); // 写入配置文件
            return true;
        }else{
            return false;
        }
    }
}
