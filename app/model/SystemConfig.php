<?php
namespace app\model;

use think\Model;

/**
 * 系统配置模型
 */
class SystemConfig extends BaseModel
{



    protected function getOptions(): array 
    {
        // 所有的参数配置统一返回
        return [
            'type' => [
                'id' => 'integer',
                'sort' => 'integer',
                'status' => 'integer',
                'options' => 'json', //自动解析options字段为数组
            ]
        ];
    }

    /**
     * 根据配置键名获取记录
     */
    public function getByName(string $name): ?self
    {
        return $this->where('name', $name)->find();
    }
}
