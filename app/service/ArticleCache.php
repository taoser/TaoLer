<?php
namespace app\service;

use think\facade\Cache;
use think\facade\Log;

class ArticleCache
{
    // 缓存文章列表
    private const CACHE_ARTICLE_LIST = 'article_list';

    // 缓存文章详情
    private const DETAIL_PREFIX = 'article_detail_';

    /**
     * 获取文章详情
     * @param int $id 文章ID
     * @return mixed 文章详情
     */
    public static function get(int $id): mixed
    {
        return Cache::get(self::DETAIL_PREFIX . $id);
    }

    /**
     * 获取文章详情
     * @param int $id 文章ID
     * @return mixed 文章详情
     */
    public static function set(int $id, mixed $detail): mixed
    {
        return Cache::set(self::DETAIL_PREFIX . $id, $detail, 600);
    }

    /**
     * 删除文章详情缓存
     * @param int $id 文章ID
     * @return bool 是否删除成功
     */
    public static function del(int $id): bool
    {
        return Cache::delete(self::DETAIL_PREFIX . $id);
    }


}
 