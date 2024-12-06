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

class Taglist extends BaseModel
{
    public function article()
	{
		//评论关联文章
		return $this->belongsTo('Article','article_id','id');
	}


    public function getTagList()
    {
        //
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