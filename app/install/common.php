<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
// 检测环境是否支持可写
//define('IS_WRITE', true);

use think\facade\Session;
use think\facade\Env;

/**
 * 写入配置文件
 * @param  array $config 配置信息
 * @return string
 */
function write_config($config)
{
    if (is_array($config)) {
        //读取配置内容
        $conf = file_get_contents(Env::get('module_path') . 'data/database.tpl');
        //替换配置项
        foreach ($config as $name => $value) {
            $conf = str_replace("[{$name}]", $value, $conf);
        }
        //写入应用配置文件
        if (file_put_contents(Env::get('config_path') . 'database.php', $conf)) {
             show_msg('配置文件写入成功!');
        } else {
             show_msg('配置文件写入失败！', 'error');
            Session::set('error', true, 'install');
        }
        return true;
    }
}

/**
 * 创建数据表
 * @param $db 数据库连接资源
 * @param string $prefix 数据表前缀
 * @return bool
 */

function create_tables($db, $prefix = '') 
{
	// 导入sql数据表
    //$sql = file_get_contents('../app/install/data/taoler.sql');
    //$sql_array = preg_split("/;[\r\n]+/", $sql);
	$sql_array = load_sql_file('../app/install/data/taoler.sql');	//sql文件中sql语句转换为数组
	if (count($sql_array)) {
		$orginal = 'tao_';	//sql表前缀
		($orginal==$prefix) ? true : $sql_array = str_replace(" `{$orginal}", " `{$prefix}", $sql_array);	//替换数组中表前缀

		//开始写入表
        foreach ($sql_array as $k => $v) {
			//halt($v);
            if (!empty($v)) {
	            //$v=$v.';';
				//执行创建表
	           if (substr($v, 0, 12) == 'CREATE TABLE') {
		            $name = preg_replace("/^CREATE TABLE `(\w+)` .*/s", "\\1", $v);
		            $msg = "创建数据表{$name}";
					$res = $db->exec($v);	//？执行成功也返回0，这里有疑问
		            if ($res === false) {
		                echo "{$msg}失败\r\n";
		            }
				} elseif(substr($v, 0, 11) == 'INSERT INTO') {
					//执行插入数据
					$name = preg_replace("/^INSERT INTO `(\w+)` .*/s", "\\1", $v);
		            $msg = "插入表{$name}数据";
					$res = $db->exec($v);
					if ($res === false) {
		                echo "{$msg}失败\r\n";
		            }
				} else {
					//执行其它sql语句
					$res = $db->exec($v);
				}
            }
        }
	} else {
		return false;
	}
   return true; 
}

function register_administrator($db, $prefix, $admin) {
    //show_msg('开始注册创始人帐号...');
    $password = password_hash($admin['password'], PASSWORD_DEFAULT);
    $sql="INSERT INTO {$prefix}user(group_id,username,password,email,create_time) VALUE(1,'{$admin['username']}','{$password}','{$admin['email']}','{time()}')";
    //执行sql
    $db->execute($sql);
     //show_msg('创始人帐号注册完成！');
}

/**
 * 更新数据表
 * @param  resource $db 数据库连接资源
 * @param string $prefix
 * @author lyq <605415184@qq.com>
 */
function update_tables($db, $prefix = '') {
    //读取SQL文件
    $sql = file_get_contents(APP_PATH . 'install/data/update.sql');
    $sql = str_replace("\r", "\n", $sql);
    $sql = explode(";\n", $sql);

    //替换表前缀
    $sql = str_replace(" `tao_", " `{$prefix}", $sql);

    //开始安装
     show_msg('开始升级数据库...');
    foreach ($sql as $value) {
        $value = trim($value);
        if (empty($value)) {
            continue;
        }
        if (substr($value, 0, 12) == 'CREATE TABLE') {
            $name = preg_replace("/^CREATE TABLE `(\w+)` .*/s", "\\1", $value);
            $msg = "创建数据表{$name}";
            if (false !== $db->execute($value)) {
                 show_msg($msg . '...成功!');
            } else {
                 show_msg($msg . '...失败！', 'error');
                Session::set('error', true, 'install');
            }
        } else {
            if (substr($value, 0, 8) == 'UPDATE `') {
                $name = preg_replace("/^UPDATE `(\w+)` .*/s", "\\1", $value);
                $msg = "更新数据表{$name}";
            } else if (substr($value, 0, 11) == 'ALTER TABLE') {
                $name = preg_replace("/^ALTER TABLE `(\w+)` .*/s", "\\1", $value);
                $msg = "修改数据表{$name}";
            } else if (substr($value, 0, 11) == 'INSERT INTO') {
                $name = preg_replace("/^INSERT INTO `(\w+)` .*/s", "\\1", $value);
                $msg = "写入数据表{$name}";
            }
            if (($db->execute($value)) !== false) {
                show_msg($msg . '...成功!');
            } else {
                show_msg($msg . '...失败！', 'error');
                Session::set('error', true, 'install');
            }
        }
    }
}

/**
 * 及时显示提示信息
 * @param  string $msg 提示信息
 * @param string $class
 * @param string $jump
 */
function show_msg($msg, $class = '',$jump='') {
    echo "<script type=\"text/javascript\">showmsg(\"{$msg}\", \"{$class}\",\"{$jump}\")</script>";
    flush();
    ob_flush();
}

/**
 * 加载sql文件为分号分割的数组
 * <br />支持存储过程和函数提取，自动过滤注释
 * <br />例如: var_export(load_sql_file('mysql_routing_example/fn_cdr_parse_accountcode.sql'));
 * @param string $path 文件路径
 * @return boolean|array
 * @since 1.0 <2015-5-27> SoChishun Added.
 */
function load_sql_file($path, $fn_splitor = ';;') {
    if (!file_exists($path)) {
        return false;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $aout = [];
    $str = '';
    $skip = false;
    $fn = false;
    foreach ($lines as $line) {
        $line = trim($line);
        // 过滤注释
        if (!$line || 0 === strpos($line, '--') || 0 === strpos($line, '*') || 0 === strpos($line, '/*') || (false !== strpos($line, '*/') && strlen($line) == (strpos($line, '*/') + 2))) {
            if (!$skip && 0 === strpos($line, '/*')) {
                $skip = true;
            }
            if ($skip && false !== strpos($line, '*/') && strlen($line) == (strpos($line, '*/') + 2)) {
                $skip = false;
            }
            continue;
        }
        if ($skip) {
            continue;
        }
        // 提取存储过程和函数
        if (0 === strpos($line, 'DELIMITER ' . $fn_splitor)) {
            $fn = true;
            continue;
        }
        if (0 === strpos($line, 'DELIMITER ;')) {
            $fn = false;
            $aout[] = $str;
            $str = '';
            continue;
        }
        if ($fn) {
            $str .= $line . ' ';
            continue;
        }
        // 提取普通语句
        $str .= $line;
        if (false !== strpos($line, ';') && strlen($line) == (strpos($line, ';') + 1)) {
            $aout[] = $str;
            $str = '';
        }
    }
    return $aout;
}


/**php多维数组或字符串值字符替换

* strReplace 多维数组或字符串值字符替换

* @param  String $find    查找的字符

* @param  String $replace 替换的字符

* @param  String $array   数组或者字符串

* @return array/String $array 数组或者字符串

*/

function strReplace($find,$replace,$array){

   if(is_array($array)){

       $array=str_replace($find,$replace,$array);

       foreach ($array as $key => $val) {

           if (is_array($val)) $array[$key]=$this->strReplace($find,$replace,$array[$key]);

       }

   }else{

       $array=str_replace($find,$replace,$array);

   }

   return $array;

}