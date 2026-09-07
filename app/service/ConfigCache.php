<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Cache;

/**
 * 配置缓存服务
 * 统一管理配置项的缓存读写和清理
 */
class ConfigCacheService
{
    // 缓存标签（支持标签的驱动如redis、file）
    private const CACHE_TAG = 'config';

    // 单个配置缓存前缀
    private const SINGLE_PREFIX = 'config_';

    // 全部配置缓存键
    private const ALL_KEY = 'config_all';

    /**
     * 获取单个配置值
     */
    public static function get(string $name): mixed
    {
        return Cache::tag(self::CACHE_TAG)->get(self::SINGLE_PREFIX . $name);
    }

    /**
     * 设置单个配置缓存
     */
    public static function set(string $name, $value): void
    {
        Cache::tag(self::CACHE_TAG)->set(self::SINGLE_PREFIX . $name, $value);
    }

    /**
     * 获取全部配置缓存
     */
    public static function getAll(): mixed
    {
        return Cache::tag(self::CACHE_TAG)->get(self::ALL_KEY);
    }

    /**
     * 设置全部配置缓存
     */
    public static function setAll(array $data): void
    {
        Cache::tag(self::CACHE_TAG)->set(self::ALL_KEY, $data);
    }

    /**
     * 清理所有配置缓存
     * 在配置增删改后调用
     */
    public static function clear(): void
    {
        Cache::tag(self::CACHE_TAG)->clear();
    }

    /**
     * 删除单个配置缓存
     */
    public static function delete(string $name): void
    {
        Cache::tag(self::CACHE_TAG)->delete(self::SINGLE_PREFIX . $name);
        // 同时清理全部缓存（因为all缓存可能包含该配置）
        Cache::tag(self::CACHE_TAG)->delete(self::ALL_KEY);
    }
}