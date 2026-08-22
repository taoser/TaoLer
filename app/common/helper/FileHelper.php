<?php

namespace app\common\helper;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use ZipArchive;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use finfo;


// // 1. 安全路径过滤防穿越
// $safePath = FileHelper::filterInvalidPath($_GET['path']);

// // 2. 上传处理
// $ext = FileHelper::getFileExt($tmpFile);
// if (!FileHelper::checkSafeExt($ext, ['jpg','png','gif'])) {
//     throw new Exception("禁止上传该格式文件");
// }
// $uploadDir = FileHelper::getSafeUploadPath('upload');
// $saveName = FileHelper::generateRandomFileName($ext);
// $savePath = $uploadDir . '/' . $saveName;
// FileHelper::copyFile($tmpFile, $savePath);

// // 3. 获取目录下所有jpg/png
// $imgList = FileHelper::getDirFilesByExt('./upload', ['jpg','png'], true, true);

// // 4. 打包备份目录
// FileHelper::zipPack('./app', './backup/app_20260712.zip');

// // 5. 清理24小时临时文件
// $delNum = FileHelper::clearDirExpireFile('./runtime/temp', 86400);

// // 6. 目录占用大小
// $sizeByte = FileHelper::getDirTotalSize('./upload');
// echo FileHelper::getFileSizeText($sizeByte);

// // 7. 读取/写入配置文件
// $config = FileHelper::readFileContent('./config/demo.php');
// FileHelper::writeFileContent('./cache/temp.txt', '缓存内容');

class FileHelper
{
    /**
     * 标准化路径：统一斜杠、去除首尾多余分隔符
     * @param string $path
     * @return string
     */
    private static function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    /**
     * 过滤路径逃逸，拦截 ../ 防止目录穿越漏洞
     * @param string $path
     * @return string
     * @throws InvalidArgumentException
     */
    public static function filterInvalidPath(string $path): string
    {
        if (str_contains($path, '../') || str_contains($path, '..\\')) {
            throw new InvalidArgumentException("非法路径，禁止跨目录访问");
        }
        return self::normalizePath($path);
    }

    /**
     * 检测目录并递归创建目录，权限0775
     * @param string $dir 目录路径
     * @return bool
     * @throws Exception
     */
    public static function mkdirs(string $dir): bool
    {
        $dir = self::normalizePath($dir);
        if (!file_exists($dir)) {
            self::mkdirs(dirname($dir));
            if (!mkdir($dir, 0775, true)) {
                throw new Exception("目录创建失败：{$dir}");
            }
        }
        return true;
    }

    /**
     * 转换为以/结尾的标准目录路径
     * @param string $path
     * @return string
     */
    public static function getDirPath(string $path): string
    {
        return self::normalizePath($path) . '/';
    }

    /**
     * 获取指定目录下【一级子目录名称列表】(仅文件夹名，不含路径，不递归深层)
     * @param string $dir 目标目录
     * @return array<string>
     * @throws InvalidArgumentException
     */
    public static function getSubDirNames(string $dir): array
    {
        $dir = self::normalizePath($dir);
        if (!is_dir($dir)) {
            throw new InvalidArgumentException("目录不存在：{$dir}");
        }

        $dirs = [];
        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $item) {
            if (!$item->isDot() && $item->isDir()) {
                $dirs[] = $item->getFilename();
            }
        }
        sort($dirs);
        return $dirs;
    }

    /**
     * 仅获取目录下所有文件，自动过滤子目录
     * @param string $dir 目标目录
     * @param bool $fullPath true返回完整路径，false仅返回文件名
     * @return array<string>
     * @throws InvalidArgumentException
     */
    public static function getDirFilesOnly(string $dir, bool $fullPath = false): array
    {
        $dir = self::normalizePath($dir);
        if (!is_dir($dir)) {
            throw new InvalidArgumentException("目录不存在：{$dir}");
        }

        $files = [];
        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $item) {
            if (!$item->isDot() && $item->isFile()) {
                if ($fullPath) {
                    $files[] = self::normalizePath($item->getPathname());
                } else {
                    $files[] = $item->getFilename();
                }
            }
        }
        sort($files);
        return $files;
    }

    /**
     * 获取目录下所有文件基础名（不包含扩展名）
     * @param string $dir 目标目录
     * @return array<string>
     * @throws InvalidArgumentException
     */
    public static function getDirFileBaseNames(string $dir): array
    {
        $dir = self::normalizePath($dir);
        if (!is_dir($dir)) {
            throw new InvalidArgumentException("目录不存在：{$dir}");
        }

        $files = [];
        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $item) {
            if (!$item->isDot() && $item->isFile()) {
                $files[] = pathinfo($item->getFilename(), PATHINFO_FILENAME);
            }
        }
        sort($files);
        return $files;
    }

    /**
     * 获取目录下所有文件名（包含扩展名）
     * @param string $dir 目标目录
     * @return array<string>
     * @throws InvalidArgumentException
     */
    public static function getDirFileNames(string $dir): array
    {
        return FileHelper::getDirFilesOnly($dir, false);
    }

    /**
     * 获取目录下所有文件完整路径（包含扩展名）
     * @param string $dir 目标目录
     * @return array<string>
     * @throws InvalidArgumentException
     */
    public static function getDirFilePaths(string $dir): array
    {
        return FileHelper::getDirFilesOnly($dir, true);
    }

    /**
     * 递归列出目录下所有文件（含深层子目录），自动过滤指定目录/文件
     * @param string $dirName 根目录
     * @param array $exclude 排除项：目录名/文件名，默认过滤 . .. runtime .DS_Store
     * @param bool $fullPath true返回完整路径，false仅返回文件相对路径
     * @return array<string>
     * @throws InvalidArgumentException
     */
    public static function getDirAllFiles(string $dirName, array $exclude = [], bool $fullPath = true): array
    {
        $baseDir = self::normalizePath($dirName);
        // 安全过滤防路径穿越
        self::filterInvalidPath($baseDir);

        if (!is_dir($baseDir)) {
            throw new InvalidArgumentException("目录不存在：{$baseDir}");
        }

        // 默认排除列表 + 自定义排除合并
        $defaultExclude = ['.', '..', 'runtime', '.DS_Store'];
        $excludeList = array_unique(array_merge($defaultExclude, $exclude));
        $fileList = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $filename = $item->getFilename();
            $currentDirName = basename($item->getPath());

            // 过滤排除的目录/文件
            if (in_array($filename, $excludeList) || in_array($currentDirName, $excludeList)) {
                $iterator->getSubIterator()->next();
                continue;
            }

            // 只收集文件，跳过目录
            if (!$item->isFile()) {
                continue;
            }

            if ($fullPath) {
                $fileList[] = self::normalizePath($item->getPathname());
            } else {
                // 获取相对根目录的路径
                $relative = str_replace($baseDir, '', self::normalizePath($item->getPathname()));
                $fileList[] = ltrim($relative, '/');
            }
        }

        sort($fileList);
        return $fileList;
    }

    // ====================== 新增：文件信息工具 ======================
    /**
     * 字节转为友好大小文本 KB/MB/GB
     * @param int $bytes
     * @return string
     */
    public static function getFileSizeText(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    /**
     * 获取文件小写后缀，自动剔除url参数
     * @param string $filePath
     * @return string
     */
    public static function getFileExt(string $filePath): string
    {
        $filePath = explode('?', $filePath)[0];
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    }

    /**
     * 获取文件真实MIME类型
     * @param string $filePath
     * @return string
     */
    public static function getMimeType(string $filePath): string
    {
        if (!file_exists($filePath)) return '';
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($filePath);
    }

    /**
     * 校验是否真实图片（防后缀篡改木马）
     * @param string $filePath
     * @return bool
     */
    public static function isImage(string $filePath): bool
    {
        if (!file_exists($filePath)) return false;
        $mime = self::getMimeType($filePath);
        $allowMime = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml'];
        if (!in_array($mime, $allowMime)) return false;
        return getimagesize($filePath) !== false;
    }

    /**
     * 获取文件哈希 md5/sha1
     * @param string $filePath
     * @param string $alg md5|sha1
     * @return string
     */
    public static function getFileHash(string $filePath, string $alg = 'md5'): string
    {
        if (!file_exists($filePath)) return '';
        return hash_file($alg, $filePath);
    }

    // ====================== 新增：单文件基础操作 ======================
    /**
     * 复制单个文件，自动创建目标目录
     * @param string $source
     * @param string $dest
     * @return bool
     * @throws Exception
     */
    public static function copyFile(string $source, string $dest): bool
    {
        $source = self::filterInvalidPath($source);
        $dest = self::filterInvalidPath($dest);
        if (!is_file($source)) throw new Exception("源文件不存在：{$source}");
        self::mkdirs(dirname($dest));
        return copy($source, $dest);
    }

    /**
     * 移动/剪切文件
     * @param string $source
     * @param string $dest
     * @return bool
     * @throws Exception
     */
    public static function moveFile(string $source, string $dest): bool
    {
        $source = self::filterInvalidPath($source);
        $dest = self::filterInvalidPath($dest);
        if (!is_file($source)) throw new Exception("源文件不存在：{$source}");
        self::mkdirs(dirname($dest));
        return rename($source, $dest);
    }

    /**
     * 删除单个文件
     * @param string $file
     * @return bool
     */
    public static function deleteFile(string $file): bool
    {
        $file = self::filterInvalidPath($file);
        if (!is_file($file)) return false;
        return unlink($file);
    }

    // ====================== 新增：文件读写 ======================
    /**
     * 读取文件内容
     * @param string $path
     * @return string
     * @throws Exception
     */
    public static function readFileContent(string $path): string
    {
        $path = self::filterInvalidPath($path);
        if (!is_file($path)) throw new Exception("文件不存在：{$path}");
        return file_get_contents($path);
    }

    /**
     * 写入文件，自动创建目录
     * @param string $path
     * @param string $content
     * @param bool $append 是否追加
     * @return bool
     */
    public static function writeFileContent(string $path, string $content, bool $append = false): bool
    {
        $path = self::filterInvalidPath($path);
        self::mkdirs(dirname($path));
        $flag = $append ? FILE_APPEND : 0;
        return file_put_contents($path, $content, $flag) !== false;
    }

    // ====================== 新增：上传安全工具 ======================
    /**
     * 校验文件后缀白名单
     * @param string $ext 文件小写后缀
     * @param array $allowExt 允许后缀数组
     * @return bool
     */
    public static function checkSafeExt(string $ext, array $allowExt = []): bool
    {
        $forbid = ['php','jsp','asp','sh','bat','exe','sql','py','html','htm'];
        if (in_array($ext, $forbid)) return false;
        if (!empty($allowExt)) return in_array($ext, $allowExt);
        return true;
    }

    /**
     * 生成唯一随机文件名
     * @param string $ext 文件后缀
     * @return string
     */
    public static function generateRandomFileName(string $ext = ''): string
    {
        $name = date('YmdHis') . mt_rand(1000,9999) . uniqid();
        return empty($ext) ? $name : $name . '.' . ltrim($ext, '.');
    }

    /**
     * 按日期生成上传目录，自动创建
     * @param string $base 根上传目录
     * @return string
     */
    public static function getSafeUploadPath(string $base = 'upload'): string
    {
        $dateDir = date('Ym/d');
        $full = self::getDirPath($base) . $dateDir;
        self::mkdirs($full);
        return self::normalizePath($full);
    }

    // ====================== 新增：目录筛选遍历 ======================
    /**
     * 获取目录下指定后缀文件，支持递归
     * @param string $dir
     * @param array $exts 后缀数组 [jpg,png]
     * @param bool $recursive 是否递归
     * @param bool $fullPath 是否返回完整路径
     * @return array
     */
    public static function getDirFilesByExt(string $dir, array $exts, bool $recursive = false, bool $fullPath = false): array
    {
        $dir = self::normalizePath($dir);
        if (!is_dir($dir)) return [];
        $files = [];
        $flags = RecursiveDirectoryIterator::SKIP_DOTS;
        $iterator = $recursive
            ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, $flags))
            : new \DirectoryIterator($dir);

        foreach ($iterator as $item) {
            if (!$item->isFile()) continue;
            $ext = self::getFileExt($item->getFilename());
            if (in_array($ext, $exts)) {
                $files[] = $fullPath ? self::normalizePath($item->getPathname()) : $item->getFilename();
            }
        }
        return $files;
    }

    // ====================== 新增：压缩打包Zip ======================
    /**
     * 打包目录/文件为zip
     * @param string $source 源目录/单文件
     * @param string $zipPath 输出zip路径
     * @return bool
     * @throws Exception
     */
    public static function zipPack(string $source, string $zipPath): bool
    {
        $source = self::filterInvalidPath($source);
        $zipPath = self::filterInvalidPath($zipPath);
        if (!class_exists(ZipArchive::class)) throw new Exception("缺少ZipArchive扩展");
        self::mkdirs(dirname($zipPath));

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("创建压缩包失败");
        }

        $source = self::normalizePath($source);
        $baseName = basename($source);
        if (is_dir($source)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iterator as $file) {
                $relative = $baseName . '/' . ltrim(str_replace($source, '', self::normalizePath($file->getPathname())), '/');
                if ($file->isDir()) {
                    $zip->addEmptyDir($relative);
                } else {
                    $zip->addFile($file->getPathname(), $relative);
                }
            }
        } else {
            $zip->addFile($source, $baseName);
        }
        $zip->close();
        return true;
    }

    // ====================== 新增：目录清理运维 ======================
    /**
     * 统计目录总大小（递归）
     * @param string $dir
     * @return int bytes
     */
    public static function getDirTotalSize(string $dir): int
    {
        $dir = self::normalizePath($dir);
        if (!is_dir($dir)) return 0;
        $size = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isFile()) $size += $item->getSize();
        }
        return $size;
    }

    /**
     * 清理目录过期文件
     * @param string $dir
     * @param int $expire 过期秒数，如86400=24小时
     * @return int 删除文件数量
     */
    public static function clearDirExpireFile(string $dir, int $expire): int
    {
        $dir = self::normalizePath($dir);
        if (!is_dir($dir)) return 0;
        $delCount = 0;
        $time = time() - $expire;
        $iterator = new \DirectoryIterator($dir);
        foreach ($iterator as $item) {
            if (!$item->isDot() && $item->isFile() && $item->getMTime() < $time) {
                unlink($item->getPathname());
                $delCount++;
            }
        }
        return $delCount;
    }

    /**
     * 清空目录所有内容，保留目录本身
     * @param string $dir
     * @return bool
     */
    public static function emptyDir(string $dir): bool
    {
        $dir = self::normalizePath($dir);
        if (!is_dir($dir)) return false;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            $file->isDir() ? rmdir($path) : unlink($path);
        }
        return true;
    }

    // ====================== 原有方法无修改分割线 ======================

    /**
     * 复制文件夹及所有子文件，支持排除指定目录 比copyDirToDir()快很多倍
     * @param string $source 源目录
     * @param string $dest   目标目录
     * @param array  $exdir  需要排除的目录名数组
     * @param bool   $delete 是否复制后删除源文件（移动模式）
     * @return bool
     * @throws Exception
     */
    public static function copyDir(
        string $source,
        string $dest,
        array $exdir = ['app'],
        bool $delete = false
    ): bool {
        $source = self::getDirPath($source);
        $dest   = self::getDirPath($dest);

        if (!is_dir($source)) {
            throw new Exception("源目录不存在：{$source}");
        }

        self::mkdirs($dest);

        // 排除目录名转哈希表，查找从 O(n) 降为 O(1)
        $exdirLookup = array_flip($exdir);

        $dirIterator = new RecursiveDirectoryIterator(
            $source,
            RecursiveDirectoryIterator::SKIP_DOTS
        );

        // 在迭代层过滤：命中排除的目录整体跳过（不产出、不递归）
        $filterIterator = new \RecursiveCallbackFilterIterator(
            $dirIterator,
            static function ($current) use ($exdirLookup): bool {
                return !($current->isDir()
                    && isset($exdirLookup[$current->getFilename()]));
            }
        );

        $iterator = new RecursiveIteratorIterator(
            $filterIterator,
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            /** @var \SplFileInfo $item */
            // $subPathName = self::normalizePath($iterator->getSubPathName());

            // 用路径截取替代 getSubPathName()，避免 IDE 报未定义方法
            $relativePath = self::normalizePath(
                ltrim(substr($item->getPathname(), strlen($source)), '/\\')
            );

            $targetPath = self::normalizePath($dest . $relativePath);

            // —— 目录：仅在不存在时创建，创建失败立即抛异常 ——
            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    self::mkdirs($targetPath);
                    is_dir($targetPath)
                        || throw new Exception("创建目标目录失败：{$targetPath}");
                }
                continue;
            }

            // —— 文件：复制 + 可选删除，均做返回值校验 ——
            $sourcePath = self::normalizePath($item->getPathname());

            copy($sourcePath, $targetPath)
                || throw new Exception("复制文件失败：{$sourcePath} → {$targetPath}");

            if ($delete) {
                unlink($sourcePath)
                    || throw new Exception("删除源文件失败：{$sourcePath}");
            }
        }

        return true;
    }


    /**
     * 复制文件夹及所有子文件，支持排除指定目录
     * @param string $source 源目录
     * @param string $dest 目标目录
     * @param array $exdir 需要排除的目录名数组
     * @param bool $delete 是否复制后删除源文件
     * @return bool
     * @throws Exception
     */
    public static function copyDirToDir(string $source, string $dest, array $exdir = ['app'], bool $delete = false): bool
    {
        $source = self::getDirPath($source);
        $dest = self::getDirPath($dest);

        if (!is_dir($source)) {
            throw new Exception("源目录不存在：{$source}");
        }
        self::mkdirs($dest);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $subPathName = self::normalizePath($iterator->getSubPathName());
            $targetItemPath = self::normalizePath($dest . $subPathName);

            $currentDirName = basename($item->getPath());
            if (in_array($currentDirName, $exdir)) {
                continue;
            }

            if ($item->isDir()) {
                if (!is_dir($targetItemPath)) {
                    self::mkdirs($targetItemPath);
                }
            } else {
                copy(self::normalizePath($item->getPathname()), $targetItemPath);
                if ($delete) {
                    unlink($item->getPathname());
                }
            }
        }
        return true;
    }

    /**
     * 迭代复制文件夹内容，支持仅复制指定前缀目录
     * @param string $source 源目录
     * @param string $destination 目标目录
     * @param string $reserve 限定目录前缀，空则全部复制
     * @param bool $is_delete_source 是否复制后删除源文件
     * @return bool
     * @throws Exception
     */
    public static function copyFolder(string $source, string $destination, string $reserve = '', bool $is_delete_source = false): bool
    {
        $source = self::getDirPath($source);
        $destination = self::getDirPath($destination);
        self::mkdirs($destination);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $hasReserve = !empty($reserve);
        $errorMsg = '';

        foreach ($iterator as $file) {
            $originPath = self::normalizePath($file->getPathname());
            $relativePath = self::normalizePath(substr($originPath, strlen($source)));
            $targetPath = self::normalizePath($destination . $relativePath);

            if ($hasReserve && !str_contains($originPath, $reserve)) {
                continue;
            }

            if ($file->isDir()) {
                self::mkdirs($targetPath);
            } else {
                $targetDir = dirname($targetPath);
                if (!is_writable($targetDir)) {
                    $errorMsg .= "{$targetPath} [无写入权限]\n";
                    continue;
                }

                try {
                    copy($originPath, $targetPath);
                } catch (Exception $e) {
                    throw new Exception("复制文件夹失败：" . $e->getMessage());
                }

                if ($is_delete_source) {
                    unlink($originPath);
                }
            }
        }

        if (!empty($errorMsg)) {
            throw new Exception("以下路径无写入权限：\n" . $errorMsg);
        }
        return true;
    }

    /**
     * 递归删除目录（包含内部所有文件子目录）
     * @param string $directory
     * @return bool
     * @throws Exception
     */
    public static function deleteDir(string $directory): bool
    {
        $directory = self::normalizePath($directory);
        if (!is_dir($directory)) {
            return false;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        try {
            foreach ($iterator as $file) {
                $path = $file->getPathname();
                $file->isDir() ? rmdir($path) : unlink($path);
            }
            rmdir($directory);
        } catch (Exception $e) {
            throw new Exception("删除文件夹失败：" . $e->getMessage());
        }
        return true;
    }

    /**
     * Zip文件解压
     * @param string $file zip文件路径
     * @param string $to_path 解压目录
     * @param bool $is_delete_file 解压后删除原压缩包
     * @return bool
     * @throws Exception
     */
    public static function unZip(string $file, string $to_path, bool $is_delete_file = false): bool
    {
        $file = self::normalizePath($file);
        $to_path = self::normalizePath($to_path);

        if (!file_exists($file)) {
            throw new Exception("压缩包不存在：{$file}");
        }
        self::mkdirs($to_path);

        if (!class_exists(ZipArchive::class, false)) {
            throw new Exception("服务器未安装ZipArchive扩展，无法解压");
        }

        $zip = new ZipArchive();
        $openRes = $zip->open($file, ZIPARCHIVE::CHECKCONS);
        if ($openRes !== true) {
            throw new Exception("压缩包打开失败，错误码：{$openRes}");
        }

        $zip->extractTo($to_path);
        $zip->close();

        if ($is_delete_file) {
            unlink($file);
        }
        return true;
    }

    /**
     * 远程文件下载到本地
     * @param string $url 远程地址
     * @param string $to_path 本地保存路径
     * @return bool
     * @throws Exception
     */
    public static function downloadFile(string $url, string $to_path): bool
    {
        $to_path = self::normalizePath($to_path);
        $saveDir = dirname($to_path);
        self::mkdirs($saveDir);

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

        try {
            $client = new Client($options);
            $response = $client->get($url);
            $status = $response->getStatusCode();

            if ($status === 404) {
                throw new Exception("远程资源404不存在");
            }
            if ($status < 200 || $status >= 300) {
                throw new Exception("远程请求异常，HTTP状态码：{$status}");
            }

            $content = $response->getBody()->getContents();
            if (empty($content)) {
                throw new Exception("远程文件内容为空");
            }
            file_put_contents($to_path, $content);
        } catch (GuzzleException $e) {
            throw new Exception("下载文件网络异常：" . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("下载文件失败：" . $e->getMessage());
        }
        return true;
    }

    /**
     * 正则匹配HTML中图片src链接
     * @param string $text html文本
     * @return array<string>
     */
    public static function getImagesLink(string $text): array
    {
        $pattern = '/<img[^>]+src=["\']([^"\']+\.(jpg|jpeg|png|gif|svg|webp))["\']/i';
        $links = [];
        if (preg_match_all($pattern, $text, $matches)) {
            $links = array_unique($matches[1]);
        }
        return $links;
    }

    /**
     * DOM解析获取HTML所有图片src（更精准，兼容不规则html）
     * @param string $html
     * @return array<string>
     */
    public static function getHTMLimagesLink(string $html): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($html);
        libxml_clear_errors();

        $imgNodes = $dom->getElementsByTagName('img');
        $links = [];
        foreach ($imgNodes as $img) {
            $src = trim($img->getAttribute('src'));
            if (!empty($src)) {
                $links[] = $src;
            }
        }
        return array_unique($links);
    }

    /**
     * HTML/JS简单压缩（去除注释、多余空白）
     * @param string $html
     * @return string
     */
    public static function compressHtmlJs(string $html): string
    {
        $html = preg_replace('/<!--(?!\[if|\<\!\[endif\])(.*?)-->/is', '', $html);
        $html = preg_replace('/\/\*(.*?)\*\//is', '', $html);

        $html = preg_replace_callback(
            '/(https?:\/\/[^\s<>]+|\/\/.*)/',
            function ($match) {
                return str_starts_with($match[0], '//') ? '' : $match[0];
            },
            $html
        );

        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        return trim($html);
    }
}