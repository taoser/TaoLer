<?php

namespace app\index\controller;

use app\common\model\Article;

class Search
{
    public function getSearch(string $keywords)
    {
        //全局查询条件
        $map = []; //所有的查询条件封装到数组中
        //条件1：
        $map[] = ['status','=',1]; //这里等号不能省略

        if(!empty($keywords)){
            //条件2
            $map[] = ['title','like','%'.$keywords.'%'];
            $res = Article::where($map)->withCount('comments')->order('create_time','desc')->paginate(10);
        return $res;
        }
    }
}