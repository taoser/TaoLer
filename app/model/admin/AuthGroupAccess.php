<?php

namespace app\model\admin;

use think\Model;
use think\model\concern\SoftDelete;

class AuthGroupAccess extends Model
{
    //软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;

    //角色分配表关联管理员
    public function admin()
    {
        return $this->belongsTo('Admin','uid','id');
    }

    //角色分配表关联管理员
    public function authGroup()
    {
        return $this->belongsTo('AuthGroup','group_id','id');
    }
	
}
