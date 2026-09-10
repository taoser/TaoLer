<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-07 09:41:19
 * @LastEditTime: 2026-09-10 08:14:48
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\service\ArticleCache.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\service;

use think\facade\Cache;
use think\facade\Log;

class ArticleCache
{

    // 详情key
    private const DETAIL_PREFIX = 'article_detail_';

    // FLAG key
    private const FLAG_ARTICLE = 'flag_article_';

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
    
    /**
     * 获取FLAG文章
     * @param string $type FLAG类型
     * @return mixed FLAG文章
     */
    public static function getFlagArticles(string $type): mixed
    {
        return Cache::get(self::FLAG_ARTICLE . $type);
    }

    /**
     * 设置FLAG文章
     * @param string $type FLAG类型
     * @param mixed $flagArticle FLAG文章
     * @return mixed 设置结果
     */
    public static function setFlagArticles(string $type, mixed $flagArticle): mixed
    {
        return Cache::set(self::FLAG_ARTICLE . $type, $flagArticle, 600);
    }

    /**
     * 删除FLAG文章
     * @param string $type FLAG类型
     * @return bool 是否删除成功
     */
    public static function delFlagArticles(string $type): bool
    {
        return Cache::delete(self::FLAG_ARTICLE . $type);
    }

}
 