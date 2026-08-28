<?php
namespace app\entity;

use think\facade\Cache;
use think\exception\ValidateException;

class SystemConfig extends BaseEntity
{
    public const CACHE_KEY = 'system_config';


    /**
     * 获取全部配置【带缓存】返回键值数组 name=>value
     * @return array
     */
    public function getAllConfig(): array
    {
        $cacheData = Cache::get(self::CACHE_KEY);
        if (!empty($cacheData)) {
            return $cacheData;
        }
        $list = $this->where('status', 1)
            ->order('sort asc')
            ->select();
        $result = [];
        foreach ($list as $item) {
            $result[$item['name']] = $item['value'];
        }
        Cache::set(self::CACHE_KEY, $result);
        return $result;
    }

    /**
     * 获取单个配置值
     * @param string $name 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getValue(string $name, $default = null): mixed
    {
        $all = $this->getAllConfig();
        return $all[$name] ?? $default;
    }

    /**
     * 清除配置缓存，新增/修改/删除后必须调用
     */
    public function clearCache(): void
    {
        Cache::delete(self::CACHE_KEY);
    }

    /**
     * 保存配置项（新增/编辑）
     * @param array $data
     * @return int id
     */
    public function saveItem(array $data): int
    {
        //校验键名唯一
        if (!empty($data['id'])) {
            $exist = $this->where('name', $data['name'])
                ->where('id', '<>', $data['id'])
                ->find();
        } else {
            $exist = $this->getByName($data['name']);
        }
        if ($exist) {
            throw new ValidateException("配置键名【{$data['name']}】已存在");
        }

        if (!empty($data['id'])) {
            $row = $this->find($data['id']);
            $row->save($data);
            $id = $row->id;
        } else {
            $row = $this->create($data);
            $id = $row->id;
        }
        $this->clearCache();
        return $id;
    }

    /**
     * 删除配置项
     */
    public function deleteItem(int $id): bool
    {
        $row = $this->find($id);
        if (!$row) {
            return false;
        }
        $row->delete();
        $this->clearCache();
        return true;
    }

    /**
     * 批量保存表单提交的配置值（后台表单页面批量更新value）
     * @param array $postData ['site_name'=>'xxx','site_status'=>'1']
     */
    public function batchSaveValue(array $postData): void
    {
        $allRows = $this->select();
        foreach ($allRows as $row) {
            // 如果是switch类型，post不存在该key，则赋值0
            if ($row['type'] === 'switch' && !array_key_exists($row['name'], $postData)) {
                $row->value = 0;
                $row->save();
                continue;
            }

            if (array_key_exists($row['name'], $postData)) {
                $val = $postData[$row['name']];
                //多选数组转逗号字符串
                if (is_array($val)) {
                    $val = implode(',', $val);
                }
                $row->value = $val;
                $row->save();
            }
        }
        $this->clearCache();
    }

    /**
     * 获取分组后的配置列表，用于渲染配置表单页面
     * @return array
     */
    public function getGroupFormList(): array
    {
        $rows = $this->order('sort asc')->select();
        $groups = [];
        foreach ($rows as $r) {
            $groups[$r['group']][] = $r;
        }
        return $groups;
    }

    /**
     * 获取配置项列表（用于配置项管理列表页，增删改配置元数据）
     */
    public function getConfigList(?string $keyword, int $page = 1, int $limit = 20): array
    {
        $query = $this->order('sort asc,id desc');
        
        if($keyword){
            $query->whereOr('group', $keyword)->whereOr('name','like','%'.$keyword.'%');
        }

        $res = $query
            ->paginate([
                'page' => $page,
                'list_rows' => $limit
            ]);

        return [
            'list' => $res->items(),
            'total' => $res->total(),
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * 更新配置项排序
     * @param int $id 配置项ID
     * @param int $sort 排序值
     * @return bool
     */
    public function updateSort(int $id, int $sort): bool
    {
        if ($sort < 0) {
            throw new ValidateException('排序值不能为负数');
        }
        $row = $this->find($id);
        if (!$row) {
            throw new ValidateException('配置项不存在');
        }
        $row->sort = $sort;
        $row->save();
        $this->clearCache(); // 排序影响展示顺序，需清缓存
        return true;
    }

    /**
     * 更新配置项启用状态
     * @param int $id 配置项ID
     * @param int $status 状态 1启用 0禁用
     * @return bool
     */
    public function updateStatus(int $id, int $status): bool
    {
        // 只允许 0 或 1
        if (!in_array((int)$status, [0, 1], true)) {
            throw new ValidateException('状态值不合法');
        }
        $row = $this->find($id);
        if (!$row) {
            throw new ValidateException('配置项不存在');
        }
        $row->status = (int)$status;
        $row->save();
        $this->clearCache(); // 状态变化影响 getAllConfig 取数，需清缓存
        return true;
    }

}
