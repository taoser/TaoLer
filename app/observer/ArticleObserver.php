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
            `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
            `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
            `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '关键词',
            `description` tinytext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'seo描述',
            `cate_id` int NOT NULL COMMENT '分类id',
            `user_id` int NOT NULL COMMENT '用户id',
            `pv` int NOT NULL DEFAULT 0 COMMENT '浏览量',
            `jie` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '0未结1已结',
            `is_top` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '置顶1否0',
            `is_hot` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '推荐1否0',
            `is_reply` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '0禁评1可评',
            `has_img` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '1有图0无图',
            `has_video` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '1有视频0无',
            `has_audio` enum('1','0') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '1有音频0无',
            `read_type` enum('0','1','2','3') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '阅读权限0开放1回复可读2密码可读3私密',
            `status` enum('0','1','-1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '状态1显示0待审-1禁止',
            `title_color` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '标题颜色',
            `art_pass` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文章加密密码',
            `goods_detail_id` int NULL DEFAULT NULL COMMENT '商品ID',
            `create_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
            `update_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
            `delete_time` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '删除时间',
            `media` json NULL COMMENT '媒体image,video,audio',
            PRIMARY KEY (`id`) USING BTREE,
            INDEX `user_id`(`user_id` ASC) USING BTREE COMMENT '文章的用户索引',
            INDEX `cate_id`(`cate_id` ASC) USING BTREE COMMENT '文章分类索引',
            INDEX `idx_article_create_time`(`create_time` DESC) USING BTREE COMMENT '创建时间'
            ) ENGINE = InnoDB AUTO_INCREMENT = {$autoIncrement} CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文章表' ROW_FORMAT = Dynamic;";

            Db::execute($table);
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
        
        return true;
    }
}