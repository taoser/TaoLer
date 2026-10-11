<?php
declare(strict_types=1);

namespace app\admin\controller\service;

use app\admin\controller\AdminBaseController;
use think\facade\Db;
use think\facade\Filesystem;
use think\response\Json;

class Slider extends AdminBaseController
{

    public function index()
    {
        return view();
    }

    /* ============================================================
     *  获取树形数据（分组 + 子项）
     * ============================================================ */
    public function tree(): Json
    {
        $now = date('Y-m-d H:i:s');

        // 1. 查询所有分组
        $groups = Db::name('slider_group')
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        // 2. 查询所有子项
        $items = Db::name('slider_item')
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        // 3. 按分组ID归类子项
        $itemMap = [];
        foreach ($items as $item) {
            $itemMap[$item['group_id']][] = $item;
        }

        // 4. 组装树形结构
        $tree = [];
        foreach ($groups as $group) {
            $node = [
                'id'          => 'g_' . $group['id'],
                'real_id'     => (int)$group['id'],
                'code'        => $group['code'],
                'name'        => $group['name'],
                'description' => $group['description'],
                'type'        => 'group',
                'sort'        => (int)$group['sort'],
                'status'      => (int)$group['status'],
                'create_time' => $group['create_time'],
                'update_time' => $group['update_time'],
                'children'    => [],
                'isParent'    => true,
            ];

            if (!empty($itemMap[$group['id']])) {
                foreach ($itemMap[$group['id']] as $item) {
                    $node['children'][] = [
                        'id'           => 'i_' . $item['id'],
                        'real_id'      => (int)$item['id'],
                        'group_id'     => (int)$item['group_id'],
                        'name'         => $item['title'],
                        'description'  => $item['description'],
                        'image'        => $item['image'],
                        'url'          => $item['url'],
                        'start_time'   => $item['start_time'],
                        'end_time'     => $item['end_time'],
                        'is_effective' => $this->checkEffective($item, $now),
                        'type'         => 'item',
                        'sort'         => (int)$item['sort'],
                        'status'       => (int)$item['status'],
                        'create_time'  => $item['create_time'],
                        'update_time'  => $item['update_time'],
                        'isParent'     => false,
                    ];
                }
            }

            $tree[] = $node;
        }

        return json(['code' => 0, 'msg' => '', 'data' => $tree]);
    }

    /* ============================================================
     *  判断子项是否在生效期内
     * ============================================================ */
    private function checkEffective(array $item, string $now): bool
    {
        if (!empty($item['start_time']) && $now < $item['start_time']) {
            return false;
        }
        if (!empty($item['end_time']) && $now > $item['end_time']) {
            return false;
        }
        return true;
    }

    /* ============================================================
     *  保存分组（新增 / 编辑）
     * ============================================================ */
    public function groupSave(): Json
    {
        $id          = (int)input('post.id/d', 0);
        $code        = trim((string)input('post.code/s', ''));
        $name        = trim((string)input('post.name/s', ''));
        $description = trim((string)input('post.description/s', ''));

        // 基础校验
        if ($name === '') {
            return json(['code' => 1, 'msg' => '分组名称不能为空']);
        }
        if ($code === '') {
            return json(['code' => 1, 'msg' => '唯一标识码不能为空']);
        }
        if (!preg_match('/^[a-z][a-z0-9_]{1,49}$/', $code)) {
            return json(['code' => 1, 'msg' => '标识码只能由小写字母、数字、下划线组成，且以小写字母开头（2-50 位）']);
        }

        // 唯一性校验
        $existQuery = Db::name('slider_group')->where('code', $code);
        if ($id > 0) {
            $existQuery->where('id', '<>', $id);
        }
        if ($existQuery->count() > 0) {
            return json(['code' => 1, 'msg' => '标识码「' . $code . '」已存在，请更换']);
        }

        $data = [
            'code'        => $code,
            'name'        => $name,
            'description' => mb_substr($description, 0, 500),
            'sort'        => (int)input('post.sort/d', 0),
            'status'      => (int)input('post.status/d', 1),
            'update_time' => date('Y-m-d H:i:s'),
        ];

        try {
            if ($id > 0) {
                Db::name('slider_group')->where('id', $id)->update($data);
            } else {
                $data['create_time'] = date('Y-m-d H:i:s');
                Db::name('slider_group')->insert($data);
            }
        } catch (\Throwable $e) {
            // 兜底捕获唯一索引冲突
            if (strpos($e->getMessage(), 'uk_code') !== false) {
                return json(['code' => 1, 'msg' => '标识码已存在']);
            }
            return json(['code' => 1, 'msg' => '保存失败：' . $e->getMessage()]);
        }

        return json(['code' => 0, 'msg' => '保存成功']);
    }

    /* ============================================================
     *  删除分组（级联删除子项）
     * ============================================================ */
    public function groupDelete(): Json
    {
        $id = (int)input('post.id/d', 0);
        if ($id <= 0) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        Db::startTrans();
        try {
            Db::name('slider_item')->where('group_id', $id)->delete();
            Db::name('slider_group')->where('id', $id)->delete();
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            return json(['code' => 1, 'msg' => '删除失败：' . $e->getMessage()]);
        }

        return json(['code' => 0, 'msg' => '删除成功']);
    }

    /* ============================================================
     *  保存子项（新增 / 编辑）
     * ============================================================ */
    public function itemSave(): Json
    {
        $id          = (int)input('post.id/d', 0);
        $groupId     = (int)input('post.group_id/d', 0);
        $title       = trim((string)input('post.title/s', ''));
        $description = trim((string)input('post.description/s', ''));
        $startTime   = $this->normalizeDatetime((string)input('post.start_time/s', ''));
        $endTime     = $this->normalizeDatetime((string)input('post.end_time/s', ''));

        if ($title === '') {
            return json(['code' => 1, 'msg' => '标题不能为空']);
        }
        if ($groupId <= 0) {
            return json(['code' => 1, 'msg' => '所属分组不能为空']);
        }
        if ($startTime && $endTime && $startTime > $endTime) {
            return json(['code' => 1, 'msg' => '结束时间不能早于开始时间']);
        }

        // 校验分组是否存在
        if (!Db::name('slider_group')->where('id', $groupId)->find()) {
            return json(['code' => 1, 'msg' => '所属分组不存在']);
        }

        $data = [
            'group_id'    => $groupId,
            'title'       => $title,
            'description' => mb_substr($description, 0, 500),
            'image'       => (string)input('post.image/s', ''),
            'url'         => (string)input('post.url/s', ''),
            'start_time'  => $startTime,
            'end_time'    => $endTime,
            'sort'        => (int)input('post.sort/d', 0),
            'status'      => (int)input('post.status/d', 1),
            'update_time' => date('Y-m-d H:i:s'),
        ];

        if ($id > 0) {
            Db::name('slider_item')->where('id', $id)->update($data);
        } else {
            $data['create_time'] = date('Y-m-d H:i:s');
            Db::name('slider_item')->insert($data);
        }

        return json(['code' => 0, 'msg' => '保存成功']);
    }

    /* ============================================================
     *  删除子项
     * ============================================================ */
    public function itemDelete(): Json
    {
        $id = (int)input('post.id/d', 0);
        if ($id <= 0) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        Db::name('slider_item')->where('id', $id)->delete();
        return json(['code' => 0, 'msg' => '删除成功']);
    }

    /* ============================================================
     *  图片上传
     * ============================================================ */
    public function upload(): Json
    {
        $file = $this->request->file('file');
        if (!$file) {
            return json(['code' => 1, 'msg' => '未接收到文件']);
        }

        try {
            validate(['file' => 'fileSize:5242880|fileExt:jpg,jpeg,png,gif,webp'])
                ->check(['file' => $file]);

            $savename = Filesystem::disk('public')->putFile('slider', $file);
            $url      = '/storage/' . str_replace('\\', '/', $savename);

            return json([
                'code' => 0,
                'msg'  => '上传成功',
                'data' => ['url' => $url],
            ]);
        } catch (\Throwable $e) {
            return json(['code' => 1, 'msg' => '上传失败：' . $e->getMessage()]);
        }
    }

    /* ============================================================
     *  前台：按分组 ID 获取有效幻灯片
     * ============================================================ */
    public function active(): Json
    {
        $groupId = (int)input('get.group_id/d', 0);
        if ($groupId <= 0) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        $items = $this->queryActiveItems(
            Db::name('slider_item')->where('group_id', $groupId)
        );

        return json(['code' => 0, 'msg' => '', 'data' => $items]);
    }

    /* ============================================================
     *  前台：按分组 code 获取有效幻灯片（推荐用法）
     * ============================================================ */
    public function byCode(): Json
    {
        $code = trim((string)input('get.code/s', ''));
        if ($code === '') {
            return json(['code' => 1, 'msg' => 'code 不能为空']);
        }

        $group = Db::name('slider_group')
            ->where('code', $code)
            ->where('status', 1)
            ->find();

        if (!$group) {
            return json(['code' => 1, 'msg' => '分组不存在或已禁用']);
        }

        $items = $this->queryActiveItems(
            Db::name('slider_item')->where('group_id', (int)$group['id'])
        );

        return json([
            'code' => 0,
            'msg'  => '',
            'data' => [
                'group' => [
                    'id'          => (int)$group['id'],
                    'code'        => $group['code'],
                    'name'        => $group['name'],
                    'description' => $group['description'],
                ],
                'items' => $items,
            ],
        ]);
    }

    /* ============================================================
     *  公共：查询某分组下当前有效（启用 + 在生效期内）的子项
     * ============================================================ */
    private function queryActiveItems(\think\db\Query $query): array
    {
        $now = date('Y-m-d H:i:s');

        return $query
            ->where('status', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_time')->whereOr('start_time', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_time')->whereOr('end_time', '>=', $now);
            })
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();
    }

    /* ============================================================
     *  工具：规整 datetime 字符串
     * ============================================================ */
    private function normalizeDatetime(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || $value === '0') {
            return null;
        }
        $value = str_replace('T', ' ', $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }
        $ts = strtotime($value);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }
}