<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-09-12 16:18:30
 * @LastEditors: TaoLer
 * @Description: AuthRuleLang
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\model\AuthRuleLang.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\model;

use think\model\concern\SoftDelete;

class AuthRuleLang extends BaseModel
{
    /**
     * 关联规则
     */ 
    public function rule()
    {
        return $this->belongsTo(AuthRule::class);
    }

	
	public function searchIdAttr($query, $value, $data)
    {
        $query->where('id', $value );      
    }

}
