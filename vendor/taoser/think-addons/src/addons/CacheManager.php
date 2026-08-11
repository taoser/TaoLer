<?php
declare(strict_types=1);

namespace taoser\addons;

use think\facade\Cache;

class CacheManager
{
    private const CACHE_PREFIX = 'addons_';
    private const CACHE_TTL = 3600;

    public static function get(string $key, $default = null)
    {
        return Cache::get(self::CACHE_PREFIX . $key, $default);
    }

    public static function set(string $key, $value, int $ttl = null): bool
    {
        return Cache::set(self::CACHE_PREFIX . $key, $value, $ttl ?? self::CACHE_TTL);
    }

    public static function remember(string $key, callable $callback, int $ttl = null)
    {
        return Cache::remember(self::CACHE_PREFIX . $key, $callback, $ttl ?? self::CACHE_TTL);
    }

    public static function delete(string $key): bool
    {
        return Cache::delete(self::CACHE_PREFIX . $key);
    }

    public static function clear(): bool
    {
        $keys = ['hooks', 'addons_config', 'addons_list', 'addon_taglib'];
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