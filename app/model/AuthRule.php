<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\model\AuthRule.php
 * @Description: AuthRule
 * @LastEditTime: 2023-03-14 16:51:30
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\model;

use think\model\concern\SoftDelete;

class AuthRule extends BaseModel
{
    //软删除
	use SoftDelete;
    
    protected function getOptions(): array 
    {
        return [
            'autoWriteTimestamp'    => true,
            'deleteTime'            => 'delete_time',
            'defaultSoftDelete'     => null,
        ];
    }

	
	public function searchIdAttr($query, $value, $data)
    {
        $query->where('id', $value );      
    }

}
