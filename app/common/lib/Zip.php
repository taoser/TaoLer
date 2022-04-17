<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-25 15:07:37
 * @LastEditTime: 2022-04-16 09:43:05
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \TaoLer\app\common\lib\Zip.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */
namespace app\common\lib;

class Zip
{
	/**
	 * 压缩文件
	 * @param array $files 待压缩文件 array('d:/test/1.txt'，'d:/test/2.jpg');【文件地址为绝对路径】
	 * @param string $filePath 输出文件路径 【绝对文件地址】 如 d:/test/new.zip
	 * @return string|bool
	 */
	function zip($files, $filePath) {
		//检查参数
		if (empty($files) || empty($filePath)) {
			return false;
		}

		//压缩文件
		$zip = new \ZipArchive();
		$zip->open($filePath, \ZipArchive::CREATE);
		foreach ($files as $key => $file) {
			//检查文件是否存在
			if (!file_exists($file)) {
				return false;
			}
			$zip->addFile($file, basename($file));
		}
		$zip->close();

		return true;
	}

	/**
	 * zip解压方法
	 * @param string $filePath 压缩包所在地址 【绝对文件地址】d:/test/123.zip
	 * @param string $path 解压路径 【绝对文件目录路径】d:/test
	 * @return bool
	 */
	function unzip($filePath, $path) {
		if (empty($path) || empty($filePath)) {
			return false;
		}

		$zip = new \ZipArchive();

		if ($zip->open($filePath) === true) {
			$zip->extractTo($path);
			$zip->close();
			return true;
		} else {
			return false;
		}
	}
}
?>