<?php

namespace tao;

use RuntimeException;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use InvalidArgumentException;
use Throwable;

/**
 * 全面文件操作助手类 (PHP 8+)
 *
 * 提供文件系统操作的统一接口，涵盖文件、目录、压缩解压、权限管理、路径安全等。
 * 所有方法在失败时都会抛出明确的异常（InvalidArgumentException 或 RuntimeException），
 * 调用方可根据需要捕获并处理。
 *
 * 功能清单：
 * - 文件：读写（原子/非原子）、追加、逐行处理、复制、移动、删除、大小（格式化/字节）、
 *         MIME类型、扩展名、哈希（MD5/SHA等）、时间戳（获取/设置）、带锁回调、强制下载
 * - 目录：创建、删除（递归/保留根）、复制、移动、清空、遍历（非递归/递归）
 * - 压缩：ZIP打包（文件/目录，可选是否包含根目录）、解压（覆盖控制）、向已有ZIP追加内容
 * - 通用：权限设置（递归）、路径信息解析、路径规范化、绝对路径判断、安全路径过滤、
 *         临时文件/目录（自动清理）
 *
 * 依赖：
 * - PHP 8.0+
 * - zip 扩展（仅用于 compressToZip / extractZip / addToZip 方法，其他方法不需要）
 *
 * @author   Assistant
 * @version  2.0
 */
class FileHelper
{
    // ==================== 文件操作 ====================

    /**
     * 读取文件全部内容
     *
     * @param string $file 文件路径
     * @return string 文件内容
     *
     * @throws InvalidArgumentException 文件不存在或不可读
     * @throws RuntimeException 读取失败（例如权限问题）
     */
    public static function readFile(string $file): string
    {
        self::ensureFileReadable($file);
        $content = file_get_contents($file);
        if ($content === false) {
            throw new RuntimeException("无法读取文件内容: {$file}");
        }
        return $content;
    }

    /**
     * 逐行读取文件，适合处理大文件
     *
     * @param string   $file     文件路径
     * @param callable $callback 回调函数，接收两个参数：string $line, int $lineNumber。
     *                           若返回 false 则停止继续读取。
     * @return int 实际处理的行数
     *
     * @throws InvalidArgumentException 文件不存在或不可读
     * @throws RuntimeException 打开文件失败
     */
    public static function readFileLines(string $file, callable $callback): int
    {
        self::ensureFileReadable($file);
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new RuntimeException("无法打开文件: {$file}");
        }
        $lineCount = 0;
        while (($line = fgets($handle)) !== false) {
            $lineCount++;
            if ($callback($line, $lineCount) === false) {
                break;
            }
        }
        fclose($handle);
        return $lineCount;
    }

    /**
     * 写入内容到文件（覆盖模式）
     *
     * @param string $file    目标文件路径
     * @param string $content 要写入的内容
     * @param bool   $atomic  是否启用原子写入（先写临时文件，成功后再替换原文件）。
     *                         原子写入可防止写入过程中断导致文件损坏，但稍慢。
     * @return int 写入的字节数
     *
     * @throws RuntimeException 写入失败或原子写入重命名失败
     */
    public static function writeFile(string $file, string $content, bool $atomic = false): int
    {
        self::ensureDirectoryForFile($file);

        if ($atomic) {
            $tempFile = $file . '.' . uniqid() . '.tmp';
            $bytes = file_put_contents($tempFile, $content, LOCK_EX);
            if ($bytes === false || !rename($tempFile, $file)) {
                @unlink($tempFile);
                throw new RuntimeException("原子写入失败: {$file}");
            }
            return $bytes;
        }

        $bytes = file_put_contents($file, $content, LOCK_EX);
        if ($bytes === false) {
            throw new RuntimeException("无法写入文件: {$file}");
        }
        return $bytes;
    }

    /**
     * 追加内容到文件末尾
     *
     * @param string $file    文件路径
     * @param string $content 要追加的内容
     * @return int 写入的字节数
     *
     * @throws RuntimeException 追加失败
     */
    public static function appendToFile(string $file, string $content): int
    {
        self::ensureDirectoryForFile($file);
        $bytes = file_put_contents($file, $content, LOCK_EX | FILE_APPEND);
        if ($bytes === false) {
            throw new RuntimeException("无法追加内容到文件: {$file}");
        }
        return $bytes;
    }

    /**
     * 复制文件
     *
     * @param string $source    源文件路径
     * @param string $dest      目标文件路径
     * @param bool   $overwrite 是否覆盖已存在的目标文件
     * @return bool
     *
     * @throws InvalidArgumentException 源文件不存在或不可读
     * @throws RuntimeException 复制失败，或目标已存在且不允许覆盖
     */
    public static function copyFile(string $source, string $dest, bool $overwrite = false): bool
    {
        self::ensureFileReadable($source);
        if (file_exists($dest) && !$overwrite) {
            throw new RuntimeException("目标文件已存在且不允许覆盖: {$dest}");
        }
        self::ensureDirectoryForFile($dest);
        if (!copy($source, $dest)) {
            throw new RuntimeException("无法复制文件: {$source} -> {$dest}");
        }
        return true;
    }

    /**
     * 移动/重命名文件
     *
     * @param string $source    源文件路径
     * @param string $dest      目标文件路径
     * @param bool   $overwrite 是否覆盖已存在的目标文件
     * @return bool
     *
     * @throws InvalidArgumentException 源文件不存在或不可读
     * @throws RuntimeException 移动失败（跨设备时内部会尝试复制+删除）
     */
    public static function moveFile(string $source, string $dest, bool $overwrite = false): bool
    {
        self::ensureFileReadable($source);
        if (file_exists($dest) && !$overwrite) {
            throw new RuntimeException("目标文件已存在且不允许覆盖: {$dest}");
        }
        self::ensureDirectoryForFile($dest);
        if (!rename($source, $dest)) {
            // 跨设备移动：先复制后删除
            self::copyFile($source, $dest, $overwrite);
            self::deleteFile($source);
        }
        return true;
    }

    /**
     * 删除文件
     *
     * @param string $file 文件路径
     * @return bool
     *
     * @throws InvalidArgumentException 路径不是文件或不存在
     * @throws RuntimeException 删除失败（如权限不足）
     */
    public static function deleteFile(string $file): bool
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException("不是有效文件: {$file}");
        }
        if (!unlink($file)) {
            throw new RuntimeException("无法删除文件: {$file}");
        }
        return true;
    }

    /**
     * 获取文件大小
     *
     * @param string $file      文件路径
     * @param bool   $formatted 是否返回可读格式（如 "1.23 MB"），否则返回字节整数
     * @return int|string 字节数或格式化字符串
     *
     * @throws InvalidArgumentException 文件不存在或不可读
     * @throws RuntimeException 获取大小失败
     */
    public static function getFileSize(string $file, bool $formatted = false): int|string
    {
        self::ensureFileReadable($file);
        $bytes = filesize($file);
        if ($bytes === false) {
            throw new RuntimeException("无法获取文件大小: {$file}");
        }
        if (!$formatted) {
            return $bytes;
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = 0;
        $size = $bytes;
        while ($size >= 1024 && $factor < 4) {
            $size /= 1024;
            $factor++;
        }
        return round($size, 2) . ' ' . $units[$factor];
    }

    /**
     * 获取文件的 MIME 类型
     *
     * @param string $file 文件路径
     * @return string MIME 类型，如 text/plain
     *
     * @throws InvalidArgumentException 文件不存在或不可读
     * @throws RuntimeException finfo 扩展未安装或获取失败
     */
    public static function getMimeType(string $file): string
    {
        self::ensureFileReadable($file);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new RuntimeException("无法初始化 finfo 扩展");
        }
        $mime = finfo_file($finfo, $file);
        finfo_close($finfo);
        if ($mime === false) {
            throw new RuntimeException("无法获取 MIME 类型: {$file}");
        }
        return $mime;
    }

    /**
     * 获取文件扩展名（小写，不含点）
     *
     * @param string $file 文件路径
     * @return string 扩展名，如 'jpg'，若没有扩展名则返回空字符串
     */
    public static function getExtension(string $file): string
    {
        return strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }

    /**
     * 判断文件是否为图片
     *
     * @param string $file 文件路径
     * @return bool
     *
     * @throws InvalidArgumentException 文件不存在或不可读
     */
    public static function isImage(string $file): bool
    {
        self::ensureFileReadable($file);
        $mime = self::getMimeType($file);
        return str_starts_with($mime, 'image/');
    }

    /**
     * 判断文件是否为文本文件
     *
     * @param string $file 文件路径
     * @return bool
     *
     * @throws InvalidArgumentException 文件不存在或不可读
     */
    public static function isTextFile(string $file): bool
    {
        self::ensureFileReadable($file);
        $mime = self::getMimeType($file);
        $textTypes = [
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript',
            'application/json',
            'application/xml',
            'text/xml',
        ];
        return in_array($mime, $textTypes, true);
    }

    /**
     * 获取文件的行数（仅适用于文本文件）
     *
     * @param string $file 文件路径
     * @return int 行数
     *
     * @throws InvalidArgumentException 文件不存在或不可读
     * @throws RuntimeException 读取失败
     */
    public static function getLineCount(string $file): int
    {
        self::ensureFileReadable($file);
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new RuntimeException("无法打开文件: {$file}");
        }
        $count = 0;
        while (!feof($handle)) {
            if (fgets($handle) !== false) {
                $count++;
            }
        }
        fclose($handle);
        return $count;
    }

    /**
     * 检查文件大小是否在指定范围内
     *
     * @param string $file 文件路径
     * @param int    $minSize 最小字节数（可选，默认0）
     * @param int    $maxSize 最大字节数（可选，默认PHP_INT_MAX）
     * @return bool
     *
     * @throws InvalidArgumentException 文件不存在或不可读
     */
    public static function checkFileSizeRange(string $file, int $minSize = 0, int $maxSize = PHP_INT_MAX): bool
    {
        $size = self::getFileSize($file);
        return $size >= $minSize && $size <= $maxSize;
    }

    /**
     * 将文件内容作为数组读取（按行分割）
     *
     * @param string $file 文件路径
     * @param bool   $trim 是否去除每行两端的空白字符（包括换行符）
     * @return array 每行作为数组元素
     *
     * @throws InvalidArgumentException 文件不存在或不可读
     */
    public static function readFileToArray(string $file, bool $trim = false): array
    {
        self::ensureFileReadable($file);
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new RuntimeException("无法读取文件内容: {$file}");
        }
        if ($trim) {
            $lines = array_map('trim', $lines);
        }
        return $lines;
    }

    /**
     * 计算文件的哈希值
     *
     * @param string $file 文件路径
     * @param string $algo 哈希算法（如 'md5', 'sha1', 'sha256'），需为 hash_algos() 支持
     * @return string 哈希字符串
     *
     * @throws InvalidArgumentException 文件不可读或算法不支持
     * @throws RuntimeException 计算失败
     */
    public static function getFileHash(string $file, string $algo = 'md5'): string
    {
        self::ensureFileReadable($file);
        if (!in_array($algo, hash_algos(), true)) {
            throw new InvalidArgumentException("不支持的哈希算法: {$algo}");
        }
        $hash = hash_file($algo, $file);
        if ($hash === false) {
            throw new RuntimeException("无法计算文件哈希: {$file}");
        }
        return $hash;
    }

    /**
     * 获取文件的时间戳
     *
     * @param string $file 文件路径
     * @param string $type 时间类型：'atime' (访问), 'mtime' (修改), 'ctime' (创建/改变)
     * @return int Unix 时间戳
     *
     * @throws InvalidArgumentException 文件不可读
     * @throws RuntimeException 获取失败
     */
    public static function getFileTime(string $file, string $type = 'mtime'): int
    {
        self::ensureFileReadable($file);
        $func = match ($type) {
            'atime' => 'fileatime',
            'ctime' => 'filectime',
            default => 'filemtime',
        };
        $time = $func($file);
        if ($time === false) {
            throw new RuntimeException("无法获取文件时间戳: {$file}");
        }
        return $time;
    }

    /**
     * 设置文件修改和访问时间
     *
     * @param string   $file  文件路径
     * @param int|null $mtime 修改时间戳（默认当前时间）
     * @param int|null $atime 访问时间戳（默认同 $mtime）
     * @return bool
     *
     * @throws InvalidArgumentException 文件不可读
     * @throws RuntimeException 设置失败
     */
    public static function setFileTime(string $file, ?int $mtime = null, ?int $atime = null): bool
    {
        self::ensureFileReadable($file);
        $mtime = $mtime ?? time();
        $atime = $atime ?? $mtime;
        if (!touch($file, $mtime, $atime)) {
            throw new RuntimeException("无法设置文件时间: {$file}");
        }
        return true;
    }

    /**
     * 对文件加锁并执行回调（阻塞模式）
     *
     * @param string   $file      文件路径
     * @param string   $operation 锁类型：'read' 共享锁（读锁），'write' 排它锁（写锁）
     * @param callable $callback  回调函数，接收一个参数：文件句柄（resource）。返回值会直接返回给调用者。
     * @return mixed 回调函数的返回值
     *
     * @throws InvalidArgumentException 文件不可读
     * @throws RuntimeException 加锁失败或文件无法打开
     */
    public static function withFileLock(string $file, string $operation, callable $callback): mixed
    {
        self::ensureFileReadable($file);
        $mode = $operation === 'read' ? 'r' : 'c+';
        $lockType = $operation === 'read' ? LOCK_SH : LOCK_EX;
        $handle = fopen($file, $mode);
        if (!$handle) {
            throw new RuntimeException("无法打开文件: {$file}");
        }
        if (!flock($handle, $lockType)) {
            fclose($handle);
            throw new RuntimeException("无法获取文件锁: {$file}");
        }
        try {
            $result = $callback($handle);
            flock($handle, LOCK_UN);
            fclose($handle);
            return $result;
        } catch (Throwable $e) {
            flock($handle, LOCK_UN);
            fclose($handle);
            throw $e;
        }
    }

    // ==================== 目录操作 ====================

    /**
     * 创建目录（自动递归创建父目录）
     *
     * @param string $dir         目录路径
     * @param int    $permissions 权限模式（八进制，如 0755）
     * @return bool
     *
     * @throws RuntimeException 创建失败
     */
    public static function createDirectory(string $dir, int $permissions = 0755): bool
    {
        if (is_dir($dir)) {
            return true;
        }
        if (!mkdir($dir, $permissions, true) && !is_dir($dir)) {
            throw new RuntimeException("无法创建目录: {$dir}");
        }
        return true;
    }

    /**
     * 递归删除目录
     *
     * @param string $dir          目录路径
     * @param bool   $preserveRoot 是否保留根目录本身：
     *                             - false 删除整个目录（包括自身）
     *                             - true 仅清空目录内容，保留目录自身
     * @return bool
     *
     * @throws InvalidArgumentException 目录不存在
     * @throws RuntimeException 删除过程中某项失败（权限不足等）
     */
    public static function deleteDirectory(string $dir, bool $preserveRoot = false): bool
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException("目录不存在: {$dir}");
        }
        if (!is_writable($dir)) {
            throw new RuntimeException("目录不可写: {$dir}");
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                if (!rmdir($item->getPathname())) {
                    throw new RuntimeException("无法删除目录: {$item->getPathname()}");
                }
            } else {
                if (!unlink($item->getPathname())) {
                    throw new RuntimeException("无法删除文件: {$item->getPathname()}");
                }
            }
        }
        if (!$preserveRoot && !rmdir($dir)) {
            throw new RuntimeException("无法删除根目录: {$dir}");
        }
        return true;
    }

    /**
     * 递归复制目录
     *
     * @param string $source    源目录
     * @param string $dest      目标目录（会自动创建）
     * @param bool   $overwrite 是否覆盖已存在的文件
     * @return bool
     *
     * @throws InvalidArgumentException 源目录不存在
     * @throws RuntimeException 复制过程中出错
     */
    public static function copyDirectory(string $source, string $dest, bool $overwrite = false): bool
    {
        // 统一路径分隔符 + 去除末尾斜杠
        $source = rtrim($source, '\\/');
        $dest = rtrim($dest, '\\/');

        if (!is_dir($source)) {
            throw new InvalidArgumentException("源目录不存在: {$source}");
        }
        self::createDirectory($dest);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $target = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                self::createDirectory($target);
            } else {
                self::copyFile($item->getPathname(), $target, $overwrite);
            }
        }
        return true;
    }

    /**
     * 移动/重命名目录
     *
     * @param string $source    源目录
     * @param string $dest      目标目录
     * @param bool   $overwrite 若目标已存在是否先删除它（危险操作，请确保使用前已备份）
     * @return bool
     *
     * @throws InvalidArgumentException 源目录不存在
     * @throws RuntimeException 移动失败（跨分区时自动尝试复制+删除）
     */
    public static function moveDirectory(string $source, string $dest, bool $overwrite = false): bool
    {
        if (!is_dir($source)) {
            throw new InvalidArgumentException("源目录不存在: {$source}");
        }
        if (is_dir($dest)) {
            if (!$overwrite) {
                throw new RuntimeException("目标目录已存在且不允许覆盖: {$dest}");
            }
            self::deleteDirectory($dest, false);
        }
        if (!rename($source, $dest)) {
            // 跨分区：复制后删除源
            self::copyDirectory($source, $dest, $overwrite);
            self::deleteDirectory($source, false);
        }
        return true;
    }

    /**
     * 清空目录（删除内部所有内容，但保留目录本身）
     *
     * @param string $dir 目录路径
     * @return bool
     *
     * @throws InvalidArgumentException 目录不存在
     * @throws RuntimeException 清空过程中出错
     */
    public static function emptyDirectory(string $dir): bool
    {
        return self::deleteDirectory($dir, true);
    }

    /**
     * 获取指定目录下的所有文件夹名（非递归，不包含子目录）
     *
     * @param string $dir 目录路径
     * @return array 文件夹名称列表
     * @throws InvalidArgumentException 目录不存在或不可读
     */
    public static function getDirNames(string $dir): array
    {
        self::ensureReadableDirectory($dir);

        $folders = [];
        $iterator = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $folders[] = $item->getFilename();
            }
        }
        return $folders;
    }

    /**
     * 获取指定目录下的所有文件名（非递归，不包含子目录）
     *
     * @param string $dir 目录路径
     * @return array 文件名称列表
     * @throws InvalidArgumentException 目录不存在或不可读
     */
    public static function getDirFileNames(string $dir): array
    {
        self::ensureReadableDirectory($dir);

        $files = [];
        $iterator = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $files[] = $item->getFilename();
            }
        }
        return $files;
    }

    /**
     * 非递归扫描目录（仅直接子项）
     *
     * @param string $dir            目录路径
     * @param bool   $returnFullPath 是否返回完整路径（默认只返回名称）
     * @return array 关联数组，包含键 'dirs'（目录列表）和 'files'（文件列表）
     *
     * @throws InvalidArgumentException 目录不存在或不可读
     */
    public static function scanDir(string $dir, bool $returnFullPath = false): array
    {
        self::ensureReadableDirectory($dir);
        $result = ['dirs' => [], 'files' => []];
        $iterator = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $item) {
            $name = $returnFullPath ? $item->getPathname() : $item->getFilename();
            if ($item->isDir()) {
                $result['dirs'][] = $name;
            } else {
                $result['files'][] = $name;
            }
        }
        return $result;
    }

    /**
     * 递归扫描目录（获取所有层级的子项）
     *
     * @param string $dir            目录路径
     * @param bool   $returnFullPath 是否返回完整路径（默认返回相对于根目录的路径）
     * @return array 关联数组，包含键 'dirs' 和 'files'，值为所有子项的路径列表
     *
     * @throws InvalidArgumentException 目录不存在或不可读
     */
    public static function scanDirs(string $dir, bool $returnFullPath = false): array
    {
        self::ensureReadableDirectory($dir);
        $result = ['dirs' => [], 'files' => []];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $name = $returnFullPath ? $item->getPathname() : $iterator->getSubPathName();
            if ($item->isDir()) {
                $result['dirs'][] = $name;
            } else {
                $result['files'][] = $name;
            }
        }
        return $result;
    }

    // ==================== 压缩 / 解压 ====================

    /**
     * 将文件或目录打包为 ZIP 文件
     *
     * @param string $source         源文件或目录路径
     * @param string $destinationZip ZIP 文件完整路径（应以 .zip 结尾）
     * @param bool   $includeBaseDir 当压缩目录时，是否在 ZIP 中包含目录本身：
     *                               - true  打包后 ZIP 根目录为目录名，内部为其内容
     *                               - false 直接将目录内的所有内容打包到 ZIP 根目录
     * @param bool   $overwrite      是否覆盖已存在的 ZIP 文件
     * @return bool
     *
     * @throws InvalidArgumentException 源路径不存在
     * @throws RuntimeException zip 扩展未安装或压缩失败
     */
    public static function compressToZip(string $source, string $destinationZip, bool $includeBaseDir = true, bool $overwrite = false): bool
    {
        self::ensureZipExtension();
        if (file_exists($destinationZip) && !$overwrite) {
            throw new RuntimeException("ZIP 文件已存在且不允许覆盖: {$destinationZip}");
        }
        if (!file_exists($source)) {
            throw new InvalidArgumentException("源路径不存在: {$source}");
        }

        $zip = new \ZipArchive();
        $flags = \ZipArchive::CREATE | ($overwrite ? \ZipArchive::OVERWRITE : 0);
        if ($zip->open($destinationZip, $flags) !== true) {
            throw new RuntimeException("无法创建 ZIP 文件: {$destinationZip}");
        }

        $sourceReal = realpath($source);
        if (is_file($sourceReal)) {
            $zip->addFile($sourceReal, basename($sourceReal));
        } else {
            $baseDirName = $includeBaseDir ? basename($sourceReal) : '';
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($sourceReal, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($files as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $filePath = $file->getPathname();
                $relativePath = $baseDirName
                    ? $baseDirName . '/' . substr($filePath, strlen($sourceReal) + 1)
                    : substr($filePath, strlen($sourceReal) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        if (!$zip->close()) {
            throw new RuntimeException("关闭 ZIP 文件失败: {$destinationZip}");
        }
        return true;
    }

    /**
     * 解压 ZIP 文件到指定目录
     *
     * @param string $zipFile     ZIP 文件路径
     * @param string $destination 目标目录（会自动创建）
     * @param bool   $overwrite   是否覆盖已存在的文件（若为 false 且目标文件已存在则抛出异常）
     * @return bool
     *
     * @throws InvalidArgumentException ZIP 文件不存在或不可读
     * @throws RuntimeException zip 扩展未安装、打开 ZIP 失败或解压失败
     */
    public static function extractZip(string $zipFile, string $destination, bool $overwrite = false): bool
    {
        self::ensureZipExtension();
        if (!is_file($zipFile) || !is_readable($zipFile)) {
            throw new InvalidArgumentException("ZIP 文件不存在或不可读: {$zipFile}");
        }
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            throw new RuntimeException("无法打开 ZIP 文件: {$zipFile}");
        }
        self::createDirectory($destination);
        if (!$overwrite) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $targetPath = $destination . DIRECTORY_SEPARATOR . $filename;
                if (file_exists($targetPath)) {
                    $zip->close();
                    throw new RuntimeException("目标文件已存在且不允许覆盖: {$targetPath}");
                }
            }
        }
        if (!$zip->extractTo($destination)) {
            $zip->close();
            throw new RuntimeException("解压失败: {$zipFile} -> {$destination}");
        }
        $zip->close();
        return true;
    }

    /**
     * 向已存在的 ZIP 文件中添加文件或目录
     *
     * @param string      $zipFile     ZIP 文件路径（必须已存在）
     * @param string      $source      要添加的源文件或目录
     * @param string|null $targetPath  在 ZIP 中的目标路径（目录形式）。若为 null，则直接使用源文件名/目录名。
     *                                 例如 targetPath = 'data/'，则添加的所有文件都会放在 ZIP 的 data/ 下。
     * @return bool
     *
     * @throws InvalidArgumentException ZIP 文件不存在或源路径不存在
     * @throws RuntimeException zip 扩展未安装、打开 ZIP 失败或添加失败
     */
    public static function addToZip(string $zipFile, string $source, ?string $targetPath = null): bool
    {
        self::ensureZipExtension();
        if (!file_exists($zipFile)) {
            throw new InvalidArgumentException("ZIP 文件不存在: {$zipFile}");
        }
        if (!file_exists($source)) {
            throw new InvalidArgumentException("源路径不存在: {$source}");
        }
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            throw new RuntimeException("无法打开 ZIP 文件: {$zipFile}");
        }
        $sourceReal = realpath($source);
        if (is_file($sourceReal)) {
            $localName = $targetPath ?? basename($sourceReal);
            if (!$zip->addFile($sourceReal, $localName)) {
                $zip->close();
                throw new RuntimeException("向 ZIP 添加文件失败: {$sourceReal}");
            }
        } else {
            $baseLocal = $targetPath ? rtrim($targetPath, '/') . '/' : '';
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($sourceReal, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($files as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $relative = $baseLocal . substr($file->getPathname(), strlen($sourceReal) + 1);
                if (!$zip->addFile($file->getPathname(), $relative)) {
                    $zip->close();
                    throw new RuntimeException("向 ZIP 添加文件失败: {$file->getPathname()}");
                }
            }
        }
        if (!$zip->close()) {
            throw new RuntimeException("关闭 ZIP 文件失败: {$zipFile}");
        }
        return true;
    }

    // ==================== 通用能力 ====================

    /**
     * 设置文件或目录的权限（支持递归）
     *
     * @param string $path        路径
     * @param int    $permissions 八进制权限，如 0755
     * @param bool   $recursive   是否递归应用到所有子项（仅对目录有效）
     * @return bool
     *
     * @throws InvalidArgumentException 路径不存在
     * @throws RuntimeException 设置权限失败
     */
    public static function setPermissions(string $path, int $permissions, bool $recursive = false): bool
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("路径不存在: {$path}");
        }
        if (!$recursive || is_file($path)) {
            if (!chmod($path, $permissions)) {
                throw new RuntimeException("无法设置权限: {$path}");
            }
            return true;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            if (!chmod($item->getPathname(), $permissions)) {
                throw new RuntimeException("无法设置权限: {$item->getPathname()}");
            }
        }
        return true;
    }

    /**
     * 获取路径的详细信息（类似 pathinfo 但结构统一）
     *
     * @param string $path 文件或目录路径
     * @return array 包含键 'dirname', 'basename', 'filename', 'extension'（仅文件有）
     */
    public static function getPathInfo(string $path): array
    {
        $info = pathinfo($path);
        return [
            'dirname'   => $info['dirname'] ?? '',
            'basename'  => $info['basename'] ?? '',
            'filename'  => $info['filename'] ?? '',
            'extension' => $info['extension'] ?? null,
        ];
    }

    /**
     * 规范化路径（消除 . 和 ..，统一目录分隔符为 DIRECTORY_SEPARATOR）
     *
     * @param string $path 原始路径
     * @param bool   $useRealpath 是否使用 realpath() 解析（路径必须存在）
     * @return string 规范化后的路径
     */
    public static function normalizePath(string $path, bool $useRealpath = false): string
    {
        if ($useRealpath && file_exists($path)) {
            $real = realpath($path);
            if ($real !== false) {
                return $real;
            }
        }

        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $result = [];
        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($result);
            } else {
                $result[] = $part;
            }
        }
        $normalized = implode(DIRECTORY_SEPARATOR, $result);
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $normalized = DIRECTORY_SEPARATOR . $normalized;
        }
        // 处理 Windows 盘符
        if (preg_match('/^[A-Z]:/i', $path) && !str_contains($normalized, ':')) {
            $normalized = substr($path, 0, 2) . DIRECTORY_SEPARATOR . $normalized;
        }
        return $normalized ?: '.';
    }

    /**
     * 获取文件的完整路径（解析符号链接）
     *
     * @param string $file 文件路径
     * @return string 解析后的完整路径
     *
     * @throws InvalidArgumentException 文件不存在
     */
    public static function getRealPath(string $file): string
    {
        $realPath = realpath($file);
        if ($realPath === false) {
            throw new InvalidArgumentException("无法解析文件路径: {$file}");
        }
        return $realPath;
    }

    /**
     * 判断路径是否为绝对路径（支持 Unix 和 Windows）
     *
     * @param string $path 路径
     * @return bool
     */
    public static function isAbsolutePath(string $path): bool
    {
        return $path !== '' && (
            str_starts_with($path, '/') ||
            str_starts_with($path, '\\') ||
            preg_match('/^[A-Z]:[\\\\\\/]/i', $path) ||
            str_starts_with($path, '\\\\')
        );
    }

    /**
     * 安全路径解析：将子路径限制在基准目录内，防止目录穿越攻击
     *
     * @param string $baseDir 基准目录（必须是绝对路径且存在）
     * @param string $subPath 相对于基准目录的子路径（可包含 ../）
     * @param bool   $allowCreate 是否允许解析不存在的路径（用于预检查）
     * @return string 解析后的绝对路径（已规范化，且确保在基准目录内）
     *
     * @throws InvalidArgumentException 基准目录不存在或子路径解析后越界
     */
    public static function securePath(string $baseDir, string $subPath, bool $allowCreate = false): string
    {
        $baseReal = realpath($baseDir);
        if ($baseReal === false) {
            throw new InvalidArgumentException("基准目录不存在或无法解析: {$baseDir}");
        }

        // 防止空路径
        if (trim($subPath) === '') {
            throw new InvalidArgumentException("子路径不能为空");
        }

        $fullPath = self::normalizePath($baseReal . DIRECTORY_SEPARATOR . $subPath);

        if ($allowCreate) {
            // 对于允许创建的情况，只需检查规范化后的路径是否在基准目录内
            if (!str_starts_with($fullPath, $baseReal . DIRECTORY_SEPARATOR) && $fullPath !== $baseReal) {
                throw new InvalidArgumentException("路径越界访问: {$subPath} 不在基准目录内");
            }
            return $fullPath;
        }

        $realPath = realpath($fullPath);
        if ($realPath === false) {
            throw new InvalidArgumentException("路径不存在或无法解析: {$fullPath}");
        }
        if (!str_starts_with($realPath, $baseReal . DIRECTORY_SEPARATOR) && $realPath !== $baseReal) {
            throw new InvalidArgumentException("路径越界访问: {$subPath} 不在基准目录内");
        }
        return $realPath;
    }

    /**
     * 获取目录下的文件数量（可选递归）
     *
     * @param string $dir       目录路径
     * @param bool   $recursive 是否递归统计
     * @return int 文件数量
     *
     * @throws InvalidArgumentException 目录不存在或不可读
     */
    public static function countFiles(string $dir, bool $recursive = false): int
    {
        self::ensureReadableDirectory($dir);
        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
        } else {
            $iterator = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
        }
        $count = 0;
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 获取目录大小（递归）
     *
     * @param string $dir 目录路径
     * @param bool   $formatted 是否返回可读格式
     * @return int|string 字节数或格式化字符串
     *
     * @throws InvalidArgumentException 目录不存在或不可读
     */
    public static function getDirSize(string $dir, bool $formatted = false): int|string
    {
        self::ensureReadableDirectory($dir);
        $size = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $size += $item->getSize();
            }
        }
        if (!$formatted) {
            return $size;
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = 0;
        while ($size >= 1024 && $factor < 4) {
            $size /= 1024;
            $factor++;
        }
        return round($size, 2) . ' ' . $units[$factor];
    }

    /**
     * 创建临时文件并返回路径（文件会在脚本结束时自动删除）
     *
     * @param string      $prefix    文件名前缀
     * @param string|null $content   初始内容（可选）
     * @param string|null $directory 临时目录路径（默认使用系统临时目录）
     * @return string 临时文件绝对路径
     *
     * @throws RuntimeException 创建临时文件失败
     */
    public static function createTempFile(string $prefix = 'tmp_', ?string $content = null, ?string $directory = null): string
    {
        $dir = $directory ?? sys_get_temp_dir();
        $tempFile = tempnam($dir, $prefix);
        if ($tempFile === false) {
            throw new RuntimeException("无法创建临时文件");
        }
        if ($content !== null) {
            file_put_contents($tempFile, $content);
        }
        register_shutdown_function(function () use ($tempFile) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        });
        return $tempFile;
    }

    /**
     * 创建临时目录并返回路径（目录会在脚本结束时自动删除）
     *
     * @param string $prefix 目录名前缀
     * @return string 临时目录绝对路径
     *
     * @throws RuntimeException 创建目录失败
     */
    public static function createTempDirectory(string $prefix = 'tmp_dir_'): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . uniqid();
        if (!mkdir($dir, 0700, true)) {
            throw new RuntimeException("无法创建临时目录: {$dir}");
        }
        register_shutdown_function(function () use ($dir) {
            if (is_dir($dir)) {
                self::deleteDirectory($dir, false);
            }
        });
        return $dir;
    }

    /**
     * 强制浏览器下载文件（发送 HTTP 下载头）
     *
     * @param string      $file         服务器上的文件路径
     * @param string|null $downloadName 下载时显示的文件名（默认使用原文件名）
     * @param bool        $exit         发送后是否终止脚本执行
     *
     * @throws InvalidArgumentException 文件不可读
     */
    public static function downloadFile(string $file, ?string $downloadName = null, bool $exit = true): void
    {
        self::ensureFileReadable($file);
        $name = $downloadName ?? basename($file);
        header('Content-Description: File Transfer');
        header('Content-Type: ' . self::getMimeType($file));
        header('Content-Disposition: attachment; filename="' . addslashes($name) . '"');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        readfile($file);
        if ($exit) {
            exit;
        }
    }

    // ==================== 内部辅助方法 ====================

    /**
     * 确保文件存在且可读
     *
     * @param string $file 文件路径
     * @throws InvalidArgumentException 文件不存在或不可读
     */
    private static function ensureFileReadable(string $file): void
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException("文件不存在: {$file}");
        }
        if (!is_readable($file)) {
            throw new InvalidArgumentException("文件不可读: {$file}");
        }
    }

    /**
     * 确保目录存在且可读
     *
     * @param string $dir 目录路径
     * @throws InvalidArgumentException 目录不存在或不可读
     */
    private static function ensureReadableDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException("目录不存在: {$dir}");
        }
        if (!is_readable($dir)) {
            throw new InvalidArgumentException("目录不可读: {$dir}");
        }
    }

    /**
     * 确保文件所在的目录存在，若不存在则自动创建
     *
     * @param string $file 文件完整路径
     * @throws RuntimeException 创建目录失败
     */
    private static function ensureDirectoryForFile(string $file): void
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            self::createDirectory($dir);
        }
    }

    /**
     * 检查 zip 扩展是否已加载，未加载则抛出异常
     *
     * @throws RuntimeException zip 扩展未安装
     */
    private static function ensureZipExtension(): void
    {
        if (!extension_loaded('zip')) {
            throw new RuntimeException("PHP zip 扩展未安装，无法进行压缩/解压操作");
        }
    }
}