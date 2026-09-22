<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2022-04-20 10:45:41
 * @LastEditTime: 2026-09-22 17:22:28
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
        return $this->field('id,name,description,title')
        ->where('ename', $ename)
        ->cache(true)
        ->find();
    }

    /**
     * 热门标签
     *
     * @return array
     */
    public function getHots(): array
    {
        $data = [];

        $tagList = Taglist::fieldRaw('tag_id, count(*) as counts')
        ->group('tag_id')
        ->order('counts', 'desc')
        ->limit(30)
        ->select()
        ->column('tag_id');

        $count = count($tagList);
        if($count) {
            $data = self::field('name,ename')
            ->whereIn('id', $tagList)
            ->append(['url'])
            ->select()
            ->toArray();
        }

        return ['count' => $count, 'data' => $data];
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

    public function getUrlAttr($value, $data)
    {
        return (string) url('tag_list', ['ename' => $data['ename']])->domain(true);
    }

}