<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2022-04-20 10:45:41
 * @LastEditTime: 2022-08-14 09:23:02
 * @LastEditors: TaoLer
 * @Description: jscode和taglink设置
 * @FilePath: \github\TaoLer\app\common\model\PushJscode.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */

namespace app\model\index;

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
     * 获取分类数据
     * 1jscode 2taglink
     *
     * @param integer $type
     * @return array
     */
    public function getAllCodes(int $type)
    {
        //
        return $this->where('type',$type)->select()->toArray();
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