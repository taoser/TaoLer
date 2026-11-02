<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-11-01 21:57:59
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


}
