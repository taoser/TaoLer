<?php
namespace app\model;

use think\Model;

/**
 * 系统配置模型
 */
class SystemConfig extends Model
{
    // protected $table = 'system_config';
    // 设置时间字段为datetime日期时间格式
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'id' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
        'options' => 'json', //自动解析options字段为数组
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    /**
     * 根据配置键名获取记录
     */
    public function getByName(string $name): ?self
    {
        return $this->where('name', $name)->find();
    }
}
