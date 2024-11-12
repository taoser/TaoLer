<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\api\model\Version.php
 * @Description: Version.php
 * @LastEditTime: 2023-03-14 10:27:33
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class Version extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;


    //获取器
    /*
        public function getPnameAttr($value)
        {
            $pname = [1=>'TaoLer',2=>'TaoilSys'];
            return $pname[$value];
        }
    */
}