<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2022-04-20 10:45:41
 * @LastEditTime: 2026-09-25 08:07:01
 * @LastEditors: TaoLer
 * @Description: 文章tag设置
 * @FilePath: \TaoLer\app\entity\Tag.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */

namespace app\entity;

use think\facade\Db;
use app\exception\BusinessException;

class Tag extends BaseEntity
{

    /**
     * ename查询
     *
     * @param string $ename
     * @return void
     */
    public function getTagByEname(string $ename)
    {
        return $this->field('id,name,ename,description,title')
        ->where('ename', $ename)
        ->cache(true)
        ->find();
    }

    public function getArticleList(string $tagEname, int $page = 1, int $limit = 15)
    {
        return Cache::remember("taglist:{$tagEname}:{$page}", function() use($tagEname, $page, $limit){

            $tag = $this->getTagByEname($tagEname);
            $idArr = $tag->articles()->column('article_id');
            
            $count = count($idArr);
            if($count === 0) {
                return ['count' => 0, 'data' => []];
            }

            $data = Article::field('id,user_id,category_id,title,content,pv,create_time,pv,has_image,has_video,has_audio,media,comments_num,flags,description')
            ->whereIn('id', $idArr)
            ->where('status', 1)
            ->with(['user' => function($query){
                $query->field('id,name,nickname,avatar,vip');
            },'category' => function($query){
                $query->field('id,name,ename');
            }])
            ->order('id desc')
            ->append(['url'])
            ->select()
            ->toArray();

            return ['count' => count($data), 'data' => $data];
        }, 1200);
    }

    /**
     * 热门标签
     *
     * @return array
     */
    public function getHots(): array
    {
        $data = $this->field('id,name,ename')
        ->order('count', 'desc')
        ->limit(30)
        ->append(['url'])
        ->select();

        return ['count' => count($data), 'data' => $data];
    }

    /**
     * 标签列表树
     * @return array
     */
    public function tree(): array
    {
        $query = $this;
        $count = $query->count();
        if($count === 0) {
            return ['count' => 0, 'data' => []];
        }
        $data = $query->field('id,name,ename')->select()->toArray();

        return ['count' => $count, 'data' => $data];
    }

    /**
     * 管理端数据
     *
     * @param integer $page
     * @param integer $limit
     * @return array
     */
    public function getList(int $page = 1, int $limit = 10): array
    {
        $count = $this->count();
        if($count === 0) {
            return ['count' => 0, 'data' => []];
        }
        $data = $this->page($page, $limit)->select()->toArray();
        
        return ['count' => $count, 'data' => $data];
    }

    /**
     * 删除数据
     * @param int $id
     * @return bool
     */
    public function del(int $id): bool
    {

        Db::startTrans();
        try{
            $tag = self::find($id);
            $tag->delete();

            Db::name('article_tag')->where('tag_id', $id)->delete();
            Db::commit();
            return true;
        }catch(BusinessException $e){
            Db::rollback();
            throw new BusinessException($e->getMessage());
        }       
    }



}