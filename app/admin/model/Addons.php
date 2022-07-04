<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2022-07-02 10:39:43
 * @LastEditTime: 2022-07-02 10:42:19
 * @LastEditors: TaoLer
 * @Description: 优化版
 * @FilePath: \TaoLer\app\admin\model\Addons.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */

namespace app\admin\model;

use think\Model;
use think\model\concern\SoftDelete;

class Addons extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
	protected $createTime = 'false';
    
}