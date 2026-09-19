<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-07-30 07:19:57
 * @LastEditTime: 2026-09-19 22:36:58
 * @LastEditors: TaoLer
 * @Description: 文章标签观察者
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\common\observer\TagObserver.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\common\observer;

use think\facade\Db;

class TagObserver implements Observer
{
    public function update($data = null)
    {
        //处理标签
        $artTags = Db::name('taglist')->where('article_id', $data['id'])->column('tag_id','id');
        if(!empty($data['tagid'])) {
            $tagIdArr = explode(',', $data['tagid']);
            foreach($artTags as $aid => $tid) {
                if(!in_array($tid, $tagIdArr)){
                    //删除被取消的tag
                    Db::name('taglist')->delete($aid);
                }
            }
            //查询保留的标签
            $artTags = Db::name('taglist')->where('article_id', $data['id'])->column('tag_id');
            $tagArr = [];
            foreach($tagIdArr as $tid) {
                if(!in_array($tid, $artTags)){
                    //新标签
                    $tagArr[] = ['article_id' => $data['id'], 'tag_id'=>$tid,'create_time' => date('Y-m-d H:i:s')];
                }
            }
            //更新新标签
            Db::name('taglist')->insertAll($tagArr);
        }
    }
}