<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-10-11 09:36:21
 * @LastEditTime: 2026-10-13 21:48:06
 * @LastEditors: TaoLer
 * @Description: 幻灯片分组
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\model\SliderGroup.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\model;
use think\model\concern\SoftDelete;

class SliderGroup extends BaseModel
{
    //软删除
	use SoftDelete;
    
    protected function getOptions(): array 
    {
        return [
            'autoWriteTimestamp'    => true,
            'deleteTime'            => 'delete_time',
            'defaultSoftDelete'     => null
        ];
    }

    // 关联幻灯子项
    public function items()
    {
        return $this->hasMany(SliderItem::class, 'group_id', 'id');
    }
}
