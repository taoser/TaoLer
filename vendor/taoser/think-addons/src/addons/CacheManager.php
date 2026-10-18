<?php
declare(strict_types=1);

namespace taoser\addons;

use think\facade\Cache;

class CacheManager
{
    // 缓存键与 helper.php / AddonsSystem 实际使用的键保持一致（无前缀）
    private const CACHE_PREFIX = '';
    private const CACHE_TTL = 3600;

    public static function get(string $key, $default = null)
    {
        return Cache::get(self::CACHE_PREFIX . $key, $default);
    }

    public static function set(string $key, $value, ?int $ttl = null): bool
    {
        return Cache::set(self::CACHE_PREFIX . $key, $value, $ttl ?? self::CACHE_TTL);
    }

    public static function remember(string $key, callable $callback, ?int $ttl = null)
    {
        return Cache::remember(self::CACHE_PREFIX . $key, $callback, $ttl ?? self::CACHE_TTL);
    }

    public static function delete(string $key): bool
    {
        return Cache::delete(self::CACHE_PREFIX . $key);
    }

    public static function clear(): bool
    {
        $keys = [
            'hooks', 'addons_config', 'addons_list', 'addonslist',
            'addon_taglib', 'addons_manifest', 'hook_usage_stats', 'high_frequency_hooks',
        ];
        foreach ($keys as $key) {
            self::delete($key);
        }
        return true;
    }

    public static function invalidateAddon(string $addon): void
    {
        self::delete("addon_{$addon}_config");
        self::delete("addon_{$addon}_info");
        self::clear();
    }
}