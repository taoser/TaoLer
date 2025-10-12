<?php
/**
 * Created by PhpStorm.
 * User: Jaeger <JaegerCode@gmail.com>
 * Date: 18/12/11
 * Time: 下午6:39
 */

namespace Jaeger;


use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class Cache extends GHttp
{
    public static function remember($name, $arguments)
    {
        $cachePool = null;
        $cacheConfig = self::initCacheConfig($arguments);

        if (empty($cacheConfig['cache'])) {
            return self::$name(...$arguments);
        }
        if (is_string($cacheConfig['cache'])) {
            $cachePool = new FilesystemAdapter(

                // a string used as the subdirectory of the root cache directory, where cache
                // items will be stored
                $namespace = '',
            
                // the default lifetime (in seconds) for cache items that do not define their
                // own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
                // until the files are deleted)
                $defaultLifetime = 0,
            
                // the main cache directory (the application needs read-write permissions on it)
                // if none is specified, a directory is created inside the system temporary directory
                $directory = $cacheConfig['cache']
            );
        }else if ($cacheConfig['cache'] instanceof CacheInterface) {
            $cachePool = $cacheConfig['cache'];
        }

        $cacheKey = self::getCacheKey($name,$arguments);
        $self = self::class;
        $data = $cachePool->get($cacheKey, function (ItemInterface $item) use($self, $name, $arguments, $cacheConfig): string {
            $item->expiresAfter($cacheConfig['cache_ttl']);
            return $self::$name(...$arguments);
        });
        return $data;
    }

    protected static function initCacheConfig($arguments)
    {
        $cacheConfig = [
            'cache' => null,
            'cache_ttl' => null
        ];
        if(!empty($arguments[2])) {
            $cacheConfig = array_merge([
                'cache' => null,
                'cache_ttl' => null
            ],$arguments[2]);
        }
        return $cacheConfig;
    }

    protected static function getCacheKey($name, $arguments)
    {
        return md5($name.'_'.json_encode($arguments));
    }
}