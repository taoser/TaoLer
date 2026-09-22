<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-07-30 07:19:57
 * @LastEditTime: 2026-09-22 18:19:10
 * @LastEditors: TaoLer
 * @Description: 文章标签观察者
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\common\observer\TagObserver.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\common\observer;

use think\facade\Db;
use app\entity\Article;
use app\entity\Tag;

class TagObserver implements Observer
{
    public function update(array $data, Article $article)
    {
        // 有 tag_ids 时，用 sync() 同步中间表（自动增删）
        if (!empty($data['tag_ids'])) {
            $tagIdArr = explode(',', $data['tag_ids']);
            $article->tags()->sync($tagIdArr);
        } else {
            // 3. tag_ids 为空时，清除所有标签关联
            $tagIdArr = $article->tags()->column('tag_id');
            if (!empty($tagIdArr)) {
                $article->tags()->detach($tagIdArr);
            }
        }
        
        // 更新article_count
        if(!empty($tagIdArr)) {
            $tagArr = [];
            foreach($tagIdArr as $tagId) {
                $count = Db::name('article_tag')->where('tag_id', $tagId)->count();
                $tagArr[] = ['id' => $tagId, 'article_count' => $count];
            }
            // 批量更新标签article_count
            Tag::saveAll($tagArr);
        }
    }

}