<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2022-04-20 10:45:41
 * @LastEditTime: 2022-04-20 12:34:40
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \TaoLer\app\admin\model\PushJscode.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */

namespace app\admin\model;

use think\Model;

class PushJscode extends Model
{
    /**
     * 保存代码
     *
     * @param [type] $data
     * @return void
     */
    public function saveCode($data)
    {
        $res = $this->save($data);
        if($res == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取所有数据
     *
     * @return void
     */
    public function getAllCodes()
    {
        //
        return $this->select();
    }

    /**
     * 删除数据
     *
     * @param [type] $id
     * @return void
     */
    public function delCode($id)
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