<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\api\controller\v1\App.php
 * @Description: App.php
 * @LastEditTime: 2023-03-14 10:16:13
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class App extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    //
    public function plugins()
    {
        return $this->hasMany(Plugins::class);
    }

}