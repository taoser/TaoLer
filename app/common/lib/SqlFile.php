<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2022-08-16 14:16:40
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \TaoLer\app\common\lib\SqlFile.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
declare (strict_types = 1);

namespace app\common\lib;

use think\facade\Lang;

class SqlFile
{
	/**
	 * 加载sql文件为分号分割的数组
	 * <br />支持存储过程和函数提取，自动过滤注释
	 * <br />例如: var_export(load_sql_file('mysql_routing_example/fn_cdr_parse_accountcode.sql'));
	 * @param string $path 文件路径
	 * @return boolean|array
	 * @since 1.0 <2015-5-27> SoChishun Added.
	 */
	public function load_sql_file($path, $fn_splitor = ';;') {
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
}
