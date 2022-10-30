<?php

namespace app\common\lib;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class FileHelper
{
    /**
     * 检测目录并循环创建目录
     * @param $dir
     * @return bool
     */
    public static function mkdirs($dir)
    {
        if (!file_exists($dir)) {
            self::mkdirs(dirname($dir));
            mkdir($dir, 0755);
        }
        return true;
    }

    /**
     * 转换为/结尾的路径
     * @param $path string 文件夹路径
     * @return string
     */
    public static function getDirPath($path)
    {
        //去掉path最右侧的/号，再重新组装带/路径
        return rtrim(str_replace('\\','/',$path),'/') . '/';
    }

    /**
     * 复制文件夹文件和子文目录文件，可排除目录复制 升级+备份代码
     * @param $source
     * @param $dest
     * @param array $exdir
     * @param $delete
     * @return bool
     */
    public static function copyDir($source, $dest, array $exdir = ['app'], $delete = false)
    {
        if (!is_dir($dest)) self::mkdirs($dest);
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item) {
            if ($item->isDir()) {
                $sontDir = $dest . $iterator->getSubPathName();
                if(in_array($sontDir,$exdir)){
                    continue;
                }
                if (!is_dir($sontDir)) {
                    self::mkdirs($sontDir);
                }
            } else {
                try {
                    copy((string)$item, $dest . $iterator->getSubPathName());
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }

                if($delete) unlink($item);
            }
        }
        return true;
    }

}