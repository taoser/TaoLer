<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-07-30 07:19:57
 * @LastEditTime: 2026-09-21 22:22:03
 * @LastEditors: TaoLer
 * @Description: 文章日志观察者
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\common\observer\LogObserver.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\common\observer;

use think\facade\Db;

class LogObserver implements Observer
{
    public function update($data, $article)
    {
        // ArticlePush事件响应处理

        // 记录每天发帖量
			Db::name('user_article_log')
            ->whereDay('create_time')
            ->inc('user_postnum')
            ->update();

            //写入taglist表
			// if(!empty($data['tag_id'])) {
			// 	$tagArr = [];
			// 	$tagIdArr = explode(',', $data['tagid']);
			// 	foreach($tagIdArr as $tid) {
			// 		$tagArr[] = [ 'article_id' => $data['article_id'], 'tag_id' => $tid, 'create_time'=>date('Y-m-d H:i:s')];
			// 	}

			// 	Db::name('taglist')->insertAll($tagArr);
			// }	
        // echo '日志更新成功';
    }
}