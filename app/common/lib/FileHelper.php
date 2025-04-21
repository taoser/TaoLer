<?php

namespace app\common\lib;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use ZipArchive;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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
            ) as $item)
        {
            if ($item->isDir()) {
                // 目录
                $subtDir = str_replace('\\','/', $dest . $iterator->getSubPathName());

                if(in_array($subtDir, $exdir)){
                    continue;
                }

                if (!is_dir($subtDir)) {
                    self::mkdirs($subtDir);
                }

            } else {
            
                    $path = str_replace('\\','/', $item->getPathName());
                    copy((string)$item, $dest . $iterator->getSubPathName());

                if($delete) unlink($item);
            }
        }
        return true;
    }

    /**
     * 迭代复制文件夹内容
     *
     * @param string $source 源文件目录
     * @param string $destination 目标目录
     * @param array $reserve 只能复制保留数组内的目录
     * @param boolean $is_delete_source 是否删除源文件
     * @return boolean
     */
    public static function copyFolder(string $source, string $destination, array $reserve = [], bool $is_delete_source = false): bool {
        // 创建递归迭代器
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        try{

            foreach ($iterator as $file) {

                $relativeFile = str_replace('\\','/', $file->getPathname());
                $relativePath = str_replace('\\','/', substr($relativeFile, strlen($source)));
                $destinationPath = str_replace('\\','/', $destination . $relativePath);
        
                if ($file->isDir()) {
                    // 如果是目录
                    if(!in_array($destinationPath, $reserve)) {
                        continue;
                    }
                    
                    //创建对应的目标目录
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
    
                } else {
                    // 如果是文件，复制文件到目标位置
                    copy($relativeFile, $destinationPath);
    
                    if($is_delete_source){
                        unlink($relativeFile);
                    }
                }
            }

        } catch(Exception $e) {
            throw new Exception("复制文件夹-".$e->getMessage());
        }

        return true;
    }

    /**
     * 删除目录及文件
     *
     * @param string $directory
     * @return boolean
     */
    public static function deleteFolder(string $directory): bool {
        if (!is_dir($directory)) {
            return false;
        }
    
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
    
        try{

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }
        
            rmdir($directory);

        } catch(Exception $e) {
            throw new Exception("删除文件夹-".$e->getMessage());
        }

        return true;
    }

    /**
     * 解压缩zip
     *
     * @param string $file 文件路径
     * @param string $to_path 解压目录
     * @param boolean $is_delete_file 是否删除源文件
     * @return boolean
     */
    public static function unZip(string $file, string $to_path, bool $is_delete_file = false): bool
    {
        try{
            // 是否存在目录
            if(!file_exists($to_path)) {
                mkdir($to_path, 0777, true);
            }
            // 是否有zip扩展
            $hasZipArchive = class_exists(ZipArchive::class, false);
            if (!$hasZipArchive) {
                throw new Exception("请安装ZipArchive扩展");
            }

            // 解压zip到runtime目录
            $zip = new ZipArchive;
            if($zip->open($file, ZIPARCHIVE::CHECKCONS) === true) {
                $zip->extractTo($to_path);
                $zip->close();
            }
            // 删除原文件
            if($is_delete_file) {
                unlink($file);
            }
        } catch(Exception $e) {
            throw new Exception("解压文件-".$e->getMessage());
        }
        return true;
    }

    /**
     * 下载文件
     *
     * @param string $url 
     * @param string $to_path
     * @return boolean
     */
    public static function downloadFile(string $url, string $to_path): bool
    {
        try{
            $options = [
                'timeout' => 30,
                'connect_timeout' => 5,
                'verify' => false,
                'http_errors' => false,
                'headers' => [
                    'Referer' => '',
                    'User-Agent' => 'taoler-template',
                ]
            ];

            $client = new Client($options);
            $response = $client->get($url);
            $body = $response->getBody();
            $status = $response->getStatusCode();
            if ($status == 404) {
                throw new Exception('安装包不存在');
            }

            $zip_content = $body->getContents();
            if (empty($zip_content)) {
                throw new Exception('安装包不存在');
            }

            file_put_contents($to_path, $zip_content);

        } catch(Exception $e) {
            throw new Exception("下载文件-".$e->getMessage());
        }

        return true;
    }

}