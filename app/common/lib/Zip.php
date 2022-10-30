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

use think\Exception;

class Zip
{
    /**
     * 保持目录结构的压缩方法
     * @param string $zipFile 压缩输出文件 相对或者绝对路径
     * @param array|string $folderPaths 要压缩的目录 相对或者绝对路径
     * @return void
     */
    public static function dirZip(string $zipFile, $folderPaths)
    {
        //1. $folderPaths 路径为数组
        // 初始化zip对象
        $zip = new \ZipArchive();
        //打开压缩文件
        $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        if(is_array($folderPaths)) {
            foreach($folderPaths as $folderPath) {
                if(self::getDirSize($folderPath) == 0) {
                    continue;
                }
                // 被压缩文件绝对路径
                $rootPath = realpath($folderPath);
                // Create recursive directory iterator
                //获取所有文件数组SplFileInfo[] $files
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($rootPath),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    //要跳过所有子目录 Skip directories (they would be added automatically)
                    if (!$file->isDir()) {
                        // 真实文件路径
                        $filePath = $file->getRealPath();
                        // zip文件的相对路径
                        $relativePath = str_replace('\\','/',str_replace(root_path(), '', $filePath));
                        //添加文件到压缩包
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }
        } else {
            // 2. $folderPaths 路径为string
            if(self::getDirSize($folderPaths) == 0) {
                throw new \Exception("Directory name must not be empty.");
            };
            // 被压缩文件绝对路径
            $rootPath = realpath($folderPaths);
            // Create recursive directory iterator
            //获取所有文件数组SplFileInfo[] $files
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                //要跳过所有子目录 Skip directories (they would be added automatically)
                if (!$file->isDir()) {
                    //  要压缩的文件路径
                    $filePath = $file->getRealPath();
                    // zip目录内文件的相对路径
                    $relativePath = str_replace('\\','/',str_replace(root_path(), '', $filePath));
                    //添加文件到压缩包
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }

        $zip->close();
    }

    /**
     * 把目录内所有文件进行压缩输出
     * @param string $zipFile 压缩文件保存路径 相对路径或者绝对路径
     * @param string $folderPath 要压缩的目录 相对路径或者绝对路径
     * @return void
     */
    public static function zipDir(string $zipFile, string $folderPath)
    {
        // 初始化zip对象
        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $rootPath = realpath($folderPath);
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                // 要压缩的文件路径
                $filePath = $file->getRealPath();
                // zip目录内文件的相对路径
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                // 添加 文件 到 压缩包
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }

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
}
