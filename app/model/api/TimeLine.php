<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\api\model\TimeLine.php
 * @Description: TimeLine.php
 * @LastEditTime: 2023-03-14 10:53:06
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class TimeLine extends Model
{
    //protected $pk = 'id'; //主键
    protected $autoWriteTimestamp = true; //开启自动时间戳
    protected $createTime = 'create_time';


    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;



}