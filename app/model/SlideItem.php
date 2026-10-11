<?php
namespace app\model;
use think\model\concern\SoftDelete;

class SlideItem extends BaseModel
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

    // 关联分组
    public function group()
    {
        return $this->belongsTo(SlideGroup::class, 'group_id', 'id');
    }

    /**
     * 获取有效幻灯（供前端首页调用，datetime直接比较）
     * @param string $code 分组标识
     * @return array
     */
    public static function getValidSlideByCode(string $code): array
    {
        return self::hasWhere('group', ['code'=>$code])
            ->where('status',1)
            ->where('start_time','<=', date('Y-m-d H:i:s'))
            ->where('end_time','>=', date('Y-m-d H:i:s'))
            ->order('sort desc, id desc')
            ->select()
            ->toArray();
    }
}
