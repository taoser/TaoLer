<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-10-13 18:59:28
 * @LastEditTime: 2026-10-13 20:28:00
 * @LastEditors: TaoLer
 * @Description: 幻灯片项
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\model\SliderItem.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\model;
use think\model\concern\SoftDelete;

class SliderItem extends BaseModel
{
    //软删除
	use SoftDelete;
    
    protected function getOptions(): array 
    {
        return [
            'autoWriteTimestamp'    => true,
            'deleteTime'            => 'delete_time',
            'defaultSoftDelete'     => null,
            'json'                  => ['option'],
        ];
    }

    // 关联分组
    public function group()
    {
        return $this->belongsTo(SliderGroup::class, 'group_id', 'id');
    }

}
