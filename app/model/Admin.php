<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-11-01 13:53:51
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\model\Admin.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
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

    /**
     * 管理员多对多关联角色
     */
    public function roles()
    {
        return $this->belongsToMany(AuthRole::class, AuthAdminRole::class);
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
