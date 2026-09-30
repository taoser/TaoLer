<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-30 21:14:17
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\entity\Page.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\entity;

use app\exception\BusinessException;

class Page extends BaseEntity
{
    /**
     * 获取单页详情
     * @param int $categoryId 分类id
     * @return array
     */
    public function getSinglePageDetail(int $categoryId)
    {
        $page = $this->where('category_id', $categoryId)->find();
        if(is_null($page)) {
            throw new BusinessException('page not found. category_id: ' . $categoryId, 404);
        }
        $page->inc('pv', 1)->save();

        return $page;
    }
}