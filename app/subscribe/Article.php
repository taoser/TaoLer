<?php

namespace app\subscribe;

use think\facade\Db;

class Article
{
    public function onArticlePush($data)
    {
        // ArticlePush事件响应处理

        // 记录每天发帖量
			Db::name('user_article_log')
            ->where('id', $data['article_log_id'])
            ->inc('user_postnum')
            ->update();

            //写入taglist表
			if(!empty($data['tag_id'])) {
				$tagArr = [];
				$tagIdArr = explode(',', $data['tag_id']);
				foreach($tagIdArr as $tid) {
					$tagArr[] = [ 'article_id' => $data['article_id'], 'tag_id' => $tid, 'create_time'=>time()];
				}
				Db::name('taglist')->insertAll($tagArr);
			}	

    }

    public function onUserLogout($user)
    {
        // UserLogout事件响应处理
    }
}