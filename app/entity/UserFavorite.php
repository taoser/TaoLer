<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-11-28 17:16:34
 * @LastEditTime: 2026-11-28 17:36:16
 * @LastEditors: TaoLer
 * @Description: 用户收藏实体类
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\entity\UserFavorite.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\entity;

class UserFavorite extends BaseEntity
{
    /**
     * 获取用户收藏列表
     */
    public function getList(array $data): array
    {
        $count = $this->where('user_id', $data['user_id'])->count();
        if($count == 0){
            return [
                'count' => $count,
                'data'  => [],
            ];
        }
        
        $page = max(1, $data['page']);
        $limit = max(1, min(100, $data['limit']));

        $data = $this->where('user_id', $data['user_id'])
        ->page($page)
        ->limit($limit)
        ->select()
        ->toArray();

        return [
            'count' => $count,
            'data'  => $data,
        ];
    }
}