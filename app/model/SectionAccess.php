<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-05 08:12:25
 * @LastEditTime: 2026-10-15 14:31:11
 * @LastEditors: TaoLer
 * @Description: 
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\model\SectionAccess.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */

namespace app\model;

class SectionAccess extends BaseModel
{

protected function getOptions(): array 
    {
        return [
            'autoWriteTimestamp'    => true,
            'json'                  => ['option'],
        ];
    }
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}