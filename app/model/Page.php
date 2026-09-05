<?php

namespace app\model;

use think\model\concern\SoftDelete;

class Page extends BaseModel
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

    //文章关联栏目表
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}