<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\api\model\UpgradeAuth.php
 * @Description: UpgradeAuth.php
 * @LastEditTime: 2023-03-14 10:42:13
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class UpgradeAuth extends Model
{
    //protected $pk = 'id'; //主键
    protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';


    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    public function getAuthLevelAttr($value)
    {
        $level = [0=>'免费', 1=>'初级', 2=>'中级', 3=>'高级'];
        return $level[$value];
    }

}