<?php

namespace app\common\observer;

use think\facade\Db;

class TagObserver implements Observer
{
    public function update($data = null)
    {
        //处理标签
        $artTags = Db::name('taglist')->where('article_id', $data['id'])->column('tag_id','id');
        if(isset($data['tagid'])) {
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
                    $tagArr[] = ['article_id' => $data['id'], 'tag_id'=>$tid,'create_time'=>time()];
                }
            }
            //更新新标签
            Db::name('taglist')->insertAll($tagArr);
        }
    }
}