<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-09 20:25:10
 * @LastEditTime: 2026-09-09 22:20:44
 * @LastEditors: TaoLer
 * @Description: 文章实体
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\entity\ArticleFlag.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\entity;

use Exception;
use think\facade\Db;
use app\facade\Article;

class ArticleFlag extends BaseEntity
{
    
    public const array TYPE = [
        'is_top' => 1,
        'is_index' => 2,
        'is_good' => 3,
    ];

    /**
     * 设置文章标志
     * @param int $article_id 文章ID
     * @param int $type 标志类型 1:is_top是否置顶 2:is_index是否推荐 3:is_good是否推荐
     * @param int $value 标志值 0:否 1:是
     * @return bool
     */
    public function setFlag(int $article_id, string $type, int $value): bool
    {
        if (!in_array($type, array_keys(self::TYPE))) {
            throw new Exception('标志类型错误');
        }

        Db::startTrans();

        try {

            $typeValue = self::TYPE[$type];
            // 查询标志是否存在
            $flag = $this->where('article_id', $article_id)->where('type', $typeValue)->find();
            if (is_null($flag)) {
                // 不存在标志 value=1 则新增
                if($value == 1) {
                    $flag = new self();
                    $flag->type = $typeValue;
                    $flag->article_id = $article_id;
                    $flag->save();
                }
            } else {
                // 存在标志 value=0 则删除
                if($value == 0){
                    $flag->delete();
                }
            }
            // 更新文章标志
            $article = Article::setSuffix(self::byIdGetSuffix($article_id))->find($article_id);
            $flags = $article->flags;
            $flags[$type] = $value;
            $article->flags = $flags;
            $article->save();

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage());
        }
        
    }
}
