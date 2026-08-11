<?php
declare(strict_types=1);

namespace taoser\addons\exception;

class AddonException extends \RuntimeException
{
    public static function notFound(string $addon): self
    {
        return new self("插件 {$addon} 不存在");
    }

    public static function disabled(string $addon): self
    {
        return new self("插件 {$addon} 已禁用");
    }

    public static function installFailed(string $addon, string $message = ''): self
    {
        return new self("插件 {$addon} 安装失败: {$message}");
    }

    public static function uninstallFailed(string $addon, string $message = ''): self
    {
        return new self("插件 {$addon} 卸载失败: {$message}");
    }

    public static function invalidHookName(string $name): self
    {
        return new self("无效的钩子名称: {$name}");
    }
}