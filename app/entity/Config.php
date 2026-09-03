<?php
declare(strict_types=1);

namespace app\entity;

use think\Entity;
use app\model\Config as ConfigModel;

class Config extends Entity
{
    protected function parseModel(): string
    {
        return ConfigModel::class;
    }

    // ========== 业务方法 ==========

    /**
     * 获取所有配置，按分组整理（用于 set.html）
     */
    public static function getGroupedList(): array
    {
        $list = self::order('sort', 'asc')->select();
        $groupMap = [];
        $groupTitles = [
            'basic'   => '基本设置',
            'upload'  => '上传设置',
            'system'  => '系统设置',
            'user'    => '用户设置',
            'email'   => '邮件设置',
        ];

        foreach ($list as $item) {
            $group = $item['group'] ?: 'default';
            if (!isset($groupMap[$group])) {
                $groupMap[$group] = [
                    'group'   => $group,
                    'title'   => $groupTitles[$group] ?? $group,
                    'configs' => [],
                ];
            }
            $groupMap[$group]['configs'][] = $item->toArray();
        }

        return array_values($groupMap);
    }

    /**
     * 根据名称获取单个配置值
     */
    public static function getValue(string $name, $default = null): mixed
    {
        $cached = \app\service\ConfigCacheService::get($name);
        if ($cached !== null) {
            return $cached;
        }

        $config = self::where('name', $name)->find();
        if (!$config) {
            return $default;
        }

        $value = $config['value'];
        \app\service\ConfigCacheService::set($name, $value);
        return $value;
    }

    /**
     * 获取所有配置（键值对形式）
     */
    public static function getAllKeyValue(): array
    {
        $cached = \app\service\ConfigCacheService::getAll();
        if ($cached !== null) {
            return $cached;
        }

        $list = self::enabled()->order('sort', 'asc')->select();
        $result = [];
        foreach ($list as $item) {
            $result[$item['name']] = $item['value'];
        }

        \app\service\ConfigCacheService::setAll($result);
        return $result;
    }

    /**
     * 分页搜索（用于 list.html）
     */
    public static function searchList(array $params): array
    {
        $query = self::order('sort', 'asc');

        if (!empty($params['group'])) {
            $query->where('group', $params['group']);
        }

        if (!empty($params['keyword'])) {
            $query->where('name', 'like', '%' . $params['keyword'] . '%')
                ->whereOr('title', 'like', '%' . $params['keyword'] . '%');
        }

        $page = max(1, intval($params['page'] ?? 1));
        $limit = max(1, intval($params['limit'] ?? 15));

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return [
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
            'list'  => $list,
        ];
    }

    /**
     * 获取所有分组列表
     */
    public static function getGroupList(): array
    {
        return self::field('group')->distinct(true)
            ->where('group', '<>', '')
            ->order('group', 'asc')
            ->select()
            ->toArray();
    }

    // ========== 新增：辅助方法 ==========

    /**
     * 将 options 数组转换为前端所需的选项列表（用于渲染 select/radio/checkbox）
     * @param array $options 从模型获取的 options 数组
     * @return array [['value'=>'xxx', 'label'=>'xxx'], ...]
     */
    public static function formatOptionsForFrontend(array $options): array
    {
        return $options; // 已经符合格式
    }

    /**
     * 根据 value 从 options 中查找对应的 label
     */
    public static function getLabelByValue($value, array $options): string
    {
        foreach ($options as $item) {
            if ((string)$item['value'] === (string)$value) {
                return (string)$item['label'];
            }
        }
        return (string)$value; // 找不到则返回原值
    }
}