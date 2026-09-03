<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class Config extends BaseModel
{

     protected function getOptions(): array 
     {
    //     // 所有的参数配置统一返回
         return [
             'type' => [
                 'id' => 'integer',
                 'sort' => 'integer',
                 'status' => 'integer',
                 'options' => 'json', //自动解析options字段为数组
    //             // 'value' => 'json', //自动解析value字段为数组
    //             // 'default_value' => 'json', //自动解析default_value字段为数组
             ]
         ];
     }

    // 需要序列化存储的类型（值需转为 JSON）
    protected static $serializeTypes = ['array', 'multi_array', 'checkbox', 'images', 'files'];

    // 需要选项的类型
    protected static $optionTypes = ['radio', 'select', 'checkbox'];

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

    /**
     * 获取器：options 自动反序列化为数组
     * 返回格式：[['value'=>'xxx', 'label'=>'xxx'], ...]
     */
    // public function getOptionsAttr($value): array
    // {
    //     if (empty($value)) {
    //         return [];
    //     }
    //     $decoded = json_decode($value, true);
    //     if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
    //         return $decoded;
    //     }
    //     // 兼容旧格式（key=value 每行，转为新格式）
    //     $lines = explode("\n", trim($value));
    //     $result = [];
    //     foreach ($lines as $line) {
    //         $line = trim($line);
    //         if (!$line) continue;
    //         if (strpos($line, '=') !== false) {
    //             [$k, $v] = explode('=', $line, 2);
    //             $result[] = ['value' => trim($k), 'label' => trim($v)];
    //         } else {
    //             $result[] = ['value' => $line, 'label' => $line];
    //         }
    //     }
    //     return $result;
    // }

    /**
     * 修改器：options 自动序列化为 JSON 字符串
     * 输入可为数组或字符串（兼容前端提交的 JSON 字符串）
     */
    // public function setOptionsAttr($value): string
    // {
    //     if (is_array($value)) {
    //         // 确保每个元素都有 value 和 label
    //         $normalized = [];
    //         foreach ($value as $item) {
    //             if (isset($item['value'])) {
    //                 $normalized[] = [
    //                     'value' => (string) $item['value'],
    //                     'label' => (string) ($item['label'] ?? $item['value']),
    //                 ];
    //             }
    //         }
    //         return json_encode($normalized, JSON_UNESCAPED_UNICODE);
    //     }
    //     // 如果传入了字符串，尝试解析 JSON
    //     if (is_string($value) && !empty($value)) {
    //         $decoded = json_decode($value, true);
    //         if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
    //             // 递归保证格式
    //             $normalized = [];
    //             foreach ($decoded as $item) {
    //                 if (isset($item['value'])) {
    //                     $normalized[] = [
    //                         'value' => (string) $item['value'],
    //                         'label' => (string) ($item['label'] ?? $item['value']),
    //                     ];
    //                 }
    //             }
    //             return json_encode($normalized, JSON_UNESCAPED_UNICODE);
    //         }
    //         // 如果解析失败，当作空
    //     }
    //     // return '[]';
    // }

    // ========== 查询范围（Scope）==========
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    public function scopeSearch($query, string $keyword)
    {
        return $query->where('name', 'like', '%' . $keyword . '%')
            ->whereOr('title', 'like', '%' . $keyword . '%');
    }

    // ========== 模型事件 ==========
    public static function onAfterWrite($model): void
    {
        \app\service\ConfigCacheService::clear();
    }

    public static function onAfterDelete($model): void
    {
        \app\service\ConfigCacheService::clear();
    }
}