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

namespace app\common\model;

use think\Model;

class Tag extends Model
{


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

   
    public function saveTag($data)
    {
        $res = $this->save($data);
        if($res == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除数据
     * @param $id
     * @return bool
     */
    public function delTag($id)
    {
        $res = $this::destroy($id);
       if($res) {
           return true;
       }
       return false;
    }

}