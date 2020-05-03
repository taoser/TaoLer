<?php

namespace app\admin\model;

use think\Model;
use think\facade\Db;
use think\facade\Session;
use think\model\concern\SoftDelete;

class AuthGroupAccess extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;
	
}
