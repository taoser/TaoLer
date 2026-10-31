<?php
namespace app\model;

use think\model\concern\SoftDelete;

class Admin extends BaseModel
{
    //软删除
    use SoftDelete;

	protected function getOptions(): array
	{
		return [
			'deleteTime'            => 'delete_time',
            'defaultSoftDelete'     => null,
		];
	}

	//管理员关联角色
/*
    public function authGroup()
    {
        return $this->belongsTo('AuthGroup','auth_group_id','id');
    }
*/

    //远程一对多管理员关联角色
    public function adminGroup()
    {
        return $this->hasManyThrough('AuthGroup', 'AuthGroupAccess','uid','id','id','group_id');
    }
	
    //管理员关联角色分配表
    public function authGroupAccess()
    {
        return $this->hasMany(AuthGroupAccess::class,'uid');
    }
	
	
}
