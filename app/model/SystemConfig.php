<?php
namespace app\model;

use think\Model;

/**
 * 系统配置模型
 */
class SystemConfig extends BaseModel
{

    // 需要序列化存储的类型（值需转为 JSON）
    protected static $serializeTypes = ['array', 'multi_array', 'checkbox', 'images', 'files'];

    // 需要选项的类型
    protected static $optionTypes = ['radio', 'select', 'checkbox'];
    
    protected function getOptions(): array 
    {
        // 所有的参数配置统一返回
        return [
            'type' => [
                'id' => 'integer',
                'group' => 'string',
                'name' => 'string',
                'title' => 'string',
                'type' => 'string',
                'default_value' => 'string',
                'value' => 'string',
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

    /**
     * 获取器：自动反序列化 value
     */
    public function getValueAttr($value, $data): mixed
    {
        if (in_array($data['type'], self::$serializeTypes)) {
            return json_decode($value, true) ?: [];
        }
        return $value;
    }

    /**
     * 修改器：自动序列化 value
     */
    public function setValueAttr($value, $data): string
    {
        if (in_array($data['type'], self::$serializeTypes)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return (string) $value;
    }

    /**
     * 获取器：自动反序列化 value
     */
    public function getDefaultValueAttr($value, $data): mixed
    {
        if (in_array($data['type'], self::$serializeTypes)) {
            return json_decode($value, true) ?: [];
        }
        return $value;
    }

    /**
     * 修改器：自动序列化 value
     */
    public function setDefaultValueAttr($value, $data): string
    {
        if (in_array($data['type'], self::$serializeTypes)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return (string) $value;
    }

}
