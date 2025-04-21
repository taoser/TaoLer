<?php
namespace app\observer;

use app\index\model\Article;
use think\facade\Db;
use Exception;

class ArticleObserver
{
    // 插入前
    public function onBeforeInsert(Article $article)
    {
        $single_table_num = config('taoler.single_table_num');
        // 表前缀
        $prefix = config('database.connections.mysql.prefix') . 'article';
        $sql = "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME REGEXP '{$prefix}_[0-9]+';";
        $tables = Db::query($sql);
        // 是否有子数据表
        if(count($tables)){
            $arr = array_column($tables,'TABLE_NAME');
            $lastTableName = end($arr);
            // 数据库最大id
            $maxId = (int) Db::table($lastTableName)->max('id');
            if($maxId === 0) {
                // 数据库为空
                $nameArr = explode("_", $lastTableName);
                // 当前空表序号
                $num =  (int) end($nameArr);
                // 空表前最大ID
                $maxId = $single_table_num * $num;
            }
        } else {
            // 仅有一张article表
            $maxId = (int) DB::name('article')->max('id');
        }
        
        // 主表无后缀
        $suffix = '';
        // 数据界限
        $num = (int) floor($maxId / $single_table_num);

        if($num > 0) {
            $suffix = "_{$num}";
            // 新表中自动插入的起始ID号
            $autoIncrement = $maxId + 1;
            // 表名
            $tableName = config('database.connections.mysql.prefix') . 'article' . $suffix; 
            // 查询表是否存在
            $sql = "SHOW TABLES LIKE '{$tableName}'";
            $tables = Db::query($sql);
            if(empty($tables)){
                $this->createTable($tableName, $autoIncrement);
            }
        }

        $article->setSuffix($suffix);
    }

    public function onBeforeUpdate(Article $article)
    {
        // dump($article->id);
        $article->setSuffix($this->byIdGetSuffix($article->id));
    }

    public function BeforeDelete(Article $article)
    {
        $article->setSuffix($this->byIdGetSuffix($article->id));
    }

    /**
     * 查、改、删时需要传入id,获取所在表的后缀
     *
     * @param integer $id
     * @return string
     */
    public function byIdGetSuffix(int $id): string
    {
        // 数据表后缀为空时，id在主表中
        $suffix = '';
        $num = (int) floor(($id - 1) / config('taoler.single_table_num'));
        // num > 0, id在对应子表中
        if($num > 0) {
            //数据表后缀
            $suffix = "_{$num}";
        }

        return $suffix;
    }

    // 自动创建表结构
    public function createTable(string $tableName, int $autoIncrement)
    {
        try{
            $table = "
            CREATE TABLE `{$tableName}`  (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
                `cate_id` int UNSIGNED NOT NULL COMMENT '分类id',
                `user_id` int UNSIGNED NOT NULL COMMENT '用户id',
                `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
                `thum_img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '缩略图',
                `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '关键词',
                `description` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'seo描述',
                `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
                `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT 'ip地址',
                `type` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '类型1文章2视频3音频',
                `status` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态1显示0待审-1禁止',
                `has_image` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '图片张数',
                `has_video` tinyint NOT NULL DEFAULT 0 COMMENT '1有视频0无',
                `has_audio` tinyint NOT NULL DEFAULT 0 COMMENT '1有音频0无',
                `is_comment` tinyint UNSIGNED NOT NULL COMMENT '可评论1是0否',
                `pv` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览量',
                `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
                `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
                `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
                `comments_num` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论数',
                `media` json NOT NULL COMMENT '媒体image,video,audio',
                `flags` json NOT NULL COMMENT '标记is_top置顶is_good推荐is_wait完结',
                PRIMARY KEY (`id`) USING BTREE,
                INDEX `user_id`(`user_id` ASC) USING BTREE COMMENT '文章的用户索引',
                INDEX `cate_id`(`cate_id` ASC) USING BTREE COMMENT '文章分类索引',
                INDEX `idx_article_create_time`(`create_time` DESC) USING BTREE COMMENT '创建时间',
                INDEX `idx_article_cid_status_dtime`(`cate_id` ASC, `status` ASC, `delete_time` ASC) USING BTREE COMMENT '分类状态时间索引'
            ) ENGINE = InnoDB AUTO_INCREMENT = {$autoIncrement} CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文章表' ROW_FORMAT = Dynamic;";

            Db::execute($table);
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
        
        return true;
    }
}