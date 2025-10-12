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
     * @param string $reserve 只能复制限定路径的目录 "view/taoler"
     * @param boolean $is_delete_source 是否删除源文件
     * @return boolean
     */
    public static function copyFolder(string $source, string $destination, string $reserve = '', bool $is_delete_source = false): bool {
        
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        // 创建递归迭代器
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $has_reserve = empty($reserve) ? false : true;
        
        $checkString = '';

        foreach ($iterator as $file) {
            // 原路径
            $relativeFile = str_replace('\\','/', $file->getPathname());
            // 路径
            $relativePath = str_replace('\\','/', substr($relativeFile, strlen($source)));
            // 文件路径
            $destinationPath = str_replace('\\','/', $destination . $relativePath);

            // 是否有限定目录及 路径中包含限定目录？
            if($has_reserve && !str_contains($relativeFile, $reserve)) {
                continue;
            }

            if ($file->isDir()) {
                //创建对应的目标目录
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

            } else {

                // 获取文件目录
                $directory = dirname($destinationPath);
                
                if (!is_writable($directory)) {
                    $checkString .= $destinationPath . '&nbsp;[<span style="color:red;">' . '无写入权限' . '</span>]<br>';
                    continue;
                }

                // 如果是文件且没有权限问题，复制文件到目标位置
                if(empty($checkString)) {
                    try{
                        copy($relativeFile, $destinationPath);
                    } catch(Exception $e) {
                        throw new Exception("复制文件夹-".$e->getMessage());
                    }
                }
                
                if($is_delete_source){
                    unlink($relativeFile);
                }
            }
        }
        
        if(!empty($checkString)) {
            throw new Exception($checkString);
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
                throw new Exception('404请求错误');
            }

            $file_content = $body->getContents();
            if (empty($file_content)) {
                throw new Exception('文件不存在');
            }

            // 提取文件所在的目录
            $directory = dirname($to_path);

            // 检查目录是否存在，如果不存在则创建
            if (!is_dir($directory)) {
                // 递归创建目录
                mkdir($directory, 0777, true);
            }

            file_put_contents($to_path, $file_content);

        } catch(Exception $e) {
            throw new Exception("下载文件-".$e->getMessage());
        }

        return true;
    }

    /**
     * 正则获取html中所有图片链接["https://www.x.com/a.jpg","https://www.y.com/b.png"]
     *
     * @param string $text
     * @return array
     */
    public static function getImagesLink(string $text): array
    {
        // 定义正则表达式来匹配图片链接，支持更多图片格式
        $pattern = '/<img[^>]+src=["\']([^"\']+\.(jpg|jpeg|png|gif|svg))["\']/i';
        $imageLinks = [];
        if (preg_match_all($pattern, $text, $matches)) {
            $imageLinks = $matches[1];
        }

        return $imageLinks;
    }

    // 得到所有html中所有图片链接
    public static function getHTMLimagesLink($html): array
    {
        // 创建 DOMDocument 对象
        $dom = new \DOMDocument();
        // 抑制错误输出，避免因 HTML 不规范而产生警告
        @$dom->loadHTML($html);

        // 获取所有的 img 标签
        $images = $dom->getElementsByTagName('img');
        $imageLinks = [];

        // 遍历 img 标签，提取 src 属性值
        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            if ($src) {
                $imageLinks[] = $src;
            }
        }

        return $imageLinks;
    }

    // 文件压缩
    public static function compressHtmlJs($html) {
        // 移除 HTML 注释
        $html = preg_replace('/<!--(?!\[if|\<\!\[endif\])(.*?)-->/is', '', $html);

        // 移除 JS 多行注释
        $html = preg_replace('/\/\*(.*?)\*\//is', '', $html);

        // 移除 JS 单行注释 排除网址外的单行注释
        $html = preg_replace_callback(
            '/(https?:\/\/[^\s<>]*|\/\/.*?(\n|$))/',
            function ($matches) {
                if (str_starts_with($matches[0], '//')) {
                    return isset($matches[2]) ? $matches[2] : '';
                }
                return $matches[0];
            }, $html);

        // 移除 JS 单行注释 正则以//开头，内容中不包含>，以换行符结尾的单行注释给移除
        // $html = preg_replace_callback('/\/\/([^>\r\n]*)(\n|\r\n)/', function ($matches) {
        //     return $matches[2];
        // }, $html);

        // 压缩 HTML 空白字符
        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);

    }

}