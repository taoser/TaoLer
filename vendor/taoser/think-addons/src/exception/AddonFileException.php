<?php
declare(strict_types=1);

namespace taoser\addons\exception;

class AddonFileException extends \RuntimeException
{
    public static function notFound(string $file): self
    {
        return new self("文件不存在: {$file}");
    }

    public static function notWritable(string $file): self
    {
        return new self("文件不可写: {$file}");
    }

    public static function pathTraversal(string $path): self
    {
        return new self("检测到路径遍历攻击: {$path}");
    }
}