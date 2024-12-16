<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2022-04-20 10:45:41
 * @LastEditTime: 2022-08-15 13:37:35
 * @LastEditors: TaoLer
 * @Description: 文章tag设置
 * @FilePath: \TaoLer\app\common\model\Taglist.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */

namespace app\index\model;

use Exception;
use app\common\model\BaseModel;
use think\facade\Cache;
use app\facade\Tag;

class Taglist extends BaseModel
{
    //评论关联文章
    public function article()
	{
		return $this->belongsTo(Article::class);
	}

    /**
     * tag文章列表
     *
     * @param string $tagEname
     * @param integer $page
     * @param integer $limit
     * @return void
     */
    public function getArticleList(string $tagEname, int $page = 1, int $limit = 15)
    {
        return Cache::remember("taglist:{$tagEname}:{$page}", function() use($tagEname, $page, $limit){
            $data = [];
            $tag = Tag::getTagByEname($tagEname);

            $taglist = self::field('article_id')
                ->where('tag_id', $tag['id'])
                ->page($page, $limit)
                ->select();

            $ids = $taglist->toArray();
            if(count($ids)) {
                $idArr = array_column($ids, 'article_id');

                $data = Article::field('id,user_id,cate_id,title,create_time,is_hot')
                ->whereIn('id', $idArr)
                ->where('status', 1)
                ->with(['user' => function($query){
                    $query->field('id,name,nickname,user_img,vip');
                },'cate' => function($query){
                    $query->field('id,catename,ename');
                }])
                ->order('id desc')
                ->append(['url'])
                ->select()
                ->toArray();
            }

            return ['count' => count($data), 'data' => $data, 'title' => $tag['name']];
        }, 1200);
    }


    public function getTagList()
    {
        return $this::select()->toArray();
    }

    public function getTag($id)
    {
        //
        return $this::find($id);
    }


    /**
     * 删除数据
     *
     * @param [type] $id
     * @return void
     */
    public function delTag($id)
    {
        //
        $res = $this::destroy($id);

       if($res == true) {
           return true;
       } else {
           return false;
       }
    }

}