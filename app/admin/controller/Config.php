<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\entity\Config as ConfigEntity;
use app\model\Config as ConfigModel;
use think\facade\Request;
use think\response\Json;
use think\facade\View;

/**
 * 配置管理控制器
 */
class Config
{
    /**
     * 配置列表页（list.html）
     */
    public function list()
    {
        // 获取分组列表用于筛选下拉
        $groups = ConfigEntity::getGroupList();

        View::assign('groups', $groups);
        return view();
    }


    // ========== API接口 ==========

    /**
     * 获取配置列表（分页+搜索）
     */
    public function getList(): Json
    {
        $params = [
            'group'   => Request::param('group', ''),
            'keyword' => Request::param('keyword', ''),
            'page'    => Request::param('page', 1),
            'limit'   => Request::param('limit', 15),
        ];

        $result = ConfigEntity::searchList($params);
        return json([
            'code'  => 0,
            'msg'   => 'success',
            'count' => $result['total'],
            'data'  => $result['list'],
        ]);
    }

    /**
     * 获取所有配置（按分组，用于set.html）
     */
    public function getAll(): Json
    {
        $data = ConfigEntity::getGroupedList();
        return json([
            'code' => 0,
            'msg'  => 'success',
            'data' => $data,
        ]);
    }

    /**
     * 添加配置
     */
    public function add(): Json
    {
        $postData = Request::post();
        // var_dump($postData); // 调试输出
        $data = $this->validateAndParse($postData);
        // var_dump($data); // 调试输出
        // halt($data);
        // 验证name唯一性
        if (ConfigModel::where('name', $data['name'])->find()) {
            return json(['code' => 1, 'msg' => '配置键名已存在']);
        }

        $config = ConfigModel::create($data);
        return json(['code' => 0, 'msg' => '添加成功', 'data' => $config]);
    }

    /**
     * 编辑配置
     */
    public function edit(): Json
    {
        $data = Request::post();
        $id = $data['id'] ?? 0;
        
        $config = ConfigModel::find($id);
        if (!$config) {
            return json(['code' => 1, 'msg' => '配置不存在']);
        }

        // 验证name唯一性（排除自身）
        $exist = ConfigModel::where('name', $data['name'])
            ->where('id', '<>', $id)
            ->find();
        if ($exist) {
            return json(['code' => 1, 'msg' => '配置键名已存在']);
        }

        $parsed = $this->validateAndParse($data);
        $config->save($parsed);
        return json(['code' => 0, 'msg' => '编辑成功']);
    }

    /**
     * 删除配置
     */
    public function delete(): Json
    {
        $id = Request::post('id');
        ConfigModel::destroy($id);
        return json(['code' => 0, 'msg' => '删除成功']);
    }

    /**
     * 更新排序（双击编辑）
     */
    public function sort(): Json
    {
        $id = Request::post('id');
        $sort = Request::post('sort');
        
        if (!is_numeric($sort)) {
            return json(['code' => 1, 'msg' => '排序值必须是数字']);
        }

        ConfigModel::where('id', $id)->update(['sort' => $sort]);
        return json(['code' => 0, 'msg' => '排序更新成功']);
    }

    /**
     * 更新状态（开关）
     */
    public function status(): Json
    {
        $id = Request::post('id');
        $status = Request::post('status', 0, 'intval');
        
        ConfigModel::where('id', $id)->update(['status' => $status]);
        return json(['code' => 0, 'msg' => '状态更新成功']);
    }

    /**
     * 批量保存配置（set.html提交）
     */
    public function batchSave(): Json
    {
        $data = Request::post('configs/a', []);
        if (empty($data)) {
            return json(['code' => 1, 'msg' => '没有要保存的数据']);
        }

        foreach ($data as $id => $value) {
            $config = ConfigModel::find($id);
            if ($config) {
                // 根据类型处理value
                $config->save(['value' => $this->parseValue($config['type'], $value)]);
            }
        }

        return json(['code' => 0, 'msg' => '保存成功']);
    }

    /**
     * 获取单个配置值（供外部调用）
     */
    public function getValue(): Json
    {
        $name = Request::param('name');
        $default = Request::param('default');
        
        $value = ConfigEntity::getValue($name, $default);
        return json(['code' => 0, 'data' => $value]);
    }

    // ========== 私有辅助方法 ==========

    /**
     * 验证并解析表单数据
     */
    private function validateAndParse(array $data): array
{
    // --- 清洗字段，防止数组注入 ---
    $data['group'] = is_string($data['group'] ?? '') ? trim($data['group']) : '';
    $data['name']  = is_string($data['name'] ?? '') ? trim($data['name']) : '';
    $data['title'] = is_string($data['title'] ?? '') ? trim($data['title']) : '';
    $data['type']  = is_string($data['type'] ?? '') ? trim($data['type']) : 'text';

    // --- 验证 ---
    if ($data['group'] === '') {
        throw new \InvalidArgumentException('分组不能为空');
    }
    if ($data['name'] === '') {
        throw new \InvalidArgumentException('键名不能为空');
    }
    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $data['name'])) {
        throw new \InvalidArgumentException('键名必须为英文、数字、下划线组合，且以字母开头');
    }
    if ($data['title'] === '') {
        throw new \InvalidArgumentException('标题不能为空');
    }

    return $data;

    // ... 后续处理 options、value 等（保持不变） ...
}

    /**
     * 解析选项（key=value每行）
     */
    private function parseOptions(string $options): array
    {
        $lines = explode("\n", trim($options));
        $result = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;
            if (strpos($line, '=') !== false) {
                [$k, $v] = explode('=', $line, 2);
                $result[trim($k)] = trim($v);
            } else {
                $result[$line] = $line;
            }
        }
        return $result;
    }

    /**
     * 根据类型解析值
     */
    private function parseValue(string $type, $value): mixed
    {
        $serializeTypes = ['array', 'multi_array', 'checkbox', 'images', 'files'];
        
        if (in_array($type, $serializeTypes)) {
            if (is_string($value)) {
                // 尝试解析JSON
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
                // array类型：按行拆分
                if ($type === 'array') {
                    return array_filter(array_map('trim', explode("\n", $value)));
                }
            }
            return $value;
        }

        // bool/switch转为int
        if (in_array($type, ['bool', 'switch'])) {
            return intval($value);
        }

        return $value;
    }
}