<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2022-04-20 10:45:41
 * @LastEditTime: 2022-08-15 12:16:41
 * @LastEditors: TaoLer
 * @Description: 文章tag设置
 * @FilePath: \TaoLer\app\common\model\Tag.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */

namespace app\index\model;

use Exception;
use app\common\model\BaseModel;
use app\facade\Taglist;

class Tag extends BaseModel
{

    /**
     * ename查询
     *
     * @param string $ename
     * @return void
     */
    public function getTagByEname(string $ename)
    {
        return self::field('id,name,keywords,description,title')->where('ename', $ename)->cache(true)->find();
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
     * 管理端数据
     *
     * @param integer $page
     * @param integer $limit
     * @return array
     */
    public function getTagList(int $page, int $limit): array
    {
        return self::paginate([
            'list_rows' => $limit,
            'page'      => $page
        ])->toArray();
    }

    /**
     * 删除数据
     * @param $id
     * @return bool
     */
    public function del($id)
    {
        $res = $this::destroy($id);
       if($res) {
           return true;
       }
       return false;
    }

    public function getUrlAttr($value, $data)
    {
        return (string) url('tag_list', ['ename' => $data['ename']])->domain(true);
    }

}