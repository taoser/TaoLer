<?php
namespace app\model;

use think\Model;

/**
 * 系统配置模型
 */
class SystemGroup extends BaseModel
{
    
    protected function getOptions(): array 
    {
        // 所有的参数配置统一返回
        return [
            'type' => [
                'id' => 'integer',
                'group_name' => 'string',
                'group_title' => 'string',
            ]
        ];
    }

    // 关联配置项
	public function config()
    {
        return $this->hasMany(SystemConfig::class);
    }


}
