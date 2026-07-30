<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\model\Admin.php
 * @Description: Admin
 * @LastEditTime: 2023-03-14 16:50:41
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Admin extends Model
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
