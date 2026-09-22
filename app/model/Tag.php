<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2022-04-20 10:45:41
 * @LastEditTime: 2026-09-22 16:53:39
 * @LastEditors: TaoLer
 * @Description: 文章tag设置
 * @FilePath: \TaoLer\app\model\Tag.php
 * @Version: V4.0.0
 * Copyright (c) 2020~2026 http://www.aieok.com All rights reserved.
 */

namespace app\model;

use think\facade\Lang;

class Tag extends BaseModel
{
    /**
     * 标签关联多篇文章
     */
    public function articles()
    {
        return $this->belongsToMany(Article::class, ArticleTag::class);
    }

    /**
     * 获取标签url
     */
    public function getUrlAttr($value, $data)
    {
        return (string) url('tag_list', ['ename' => $data['ename']])->domain(true);
    }

    public function getNameAttr($value, $data)
    {
        $lang = Lang::getLangSet();

        if($lang === 'en-us') {
            return $data['ename'];
        }
        return $data['name'];
    }

}