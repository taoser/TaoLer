<?php
declare(strict_types=1);

namespace taoser\addons;

use taoser\addons\exception\AddonFileException;

class FileValidator
{
    public static function validatePath(string $path, string $base_path): string
    {
        $real_path = realpath($path);
        $real_base = realpath($base_path);
        
        if ($real_path === false) {
            throw AddonFileException::notFound($path);
        }
        
        if ($real_base === false || strpos($real_path, $real_base) !== 0) {
            throw AddonFileException::pathTraversal($path);
        }
        
        return $real_path;
    }

    public static function validateWritable(string $path): string
    {
        if (!is_writable($path)) {
            throw AddonFileException::notWritable($path);
        }
        
        return $path;
    }

    public static function safeInclude(string $file, string $base_path): mixed
    {
        $validated_path = self::validatePath($file, $base_path);
        return include_once $validated_path;
    }
}