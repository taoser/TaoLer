<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-10-31 14:57:54
 * @LastEditTime: 2026-11-01 14:39:03
 * @LastEditors: TaoLer
 * @Description: 角色模型
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\model\AuthRole.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\model;


class AuthRole extends BaseModel
{
    protected function getOptions(): array 
    {
        return [
            'autoWriteTimestamp'    => true,
        ];
    }

    /**
     * 角色多对多关联权限表
     * @return \think\model\relation\BelongsToMany
     */
    public function rules()
    {
        return $this->belongsToMany(AuthRule::class, AuthRoleRule::class);
    }

	/**
	 * 角色多对多关联管理员
	 * @return \think\model\relation\BelongsToMany
	 */
	public function admins()
	{
		return $this->belongsToMany(Admin::class, AuthAdminRole::class);
	}


}
