<?php

namespace app\model;

use Exception;
use think\model\concern\SoftDelete;

class Collection extends BaseModel
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
	
    //收藏关联文章
	public function article()
	{
		return $this->belongsTo(Article::class);
	}

}