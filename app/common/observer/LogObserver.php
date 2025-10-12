<?php

namespace app\common\observer;

use think\facade\Db;

class LogObserver implements Observer
{
    public function update($data = null)
    {
        // ArticlePush事件响应处理

        // 记录每天发帖量
			Db::name('user_article_log')
            ->whereDay('create_time')
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
        // echo '日志更新成功';
    }
}