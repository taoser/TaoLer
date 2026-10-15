<?php
declare(strict_types=1);

namespace app\admin\controller\service;

use think\Request;
use think\Response;
use think\facade\Db;
use think\facade\Filesystem;
use think\response\Json;
use app\admin\controller\AdminBaseController;

class Slider extends AdminBaseController
{

    public function index()
    {
        return view();
    }

    /* ============================================================
     *  获取树形数据（分组 + 子项）
     * ============================================================ */
    public function list(): Response
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
            ->json(['option'])
            ->select()
            ->toArray();

            // dump($items);
            // return json(['code' => 0, 'msg' => '', 'data' => $items]);

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
                'title'       => $group['title'],
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
                        'title'        => $item['title'],
                        'description'  => $item['description'],
                        'image'        => $item['image'],
                        'url'          => $item['url'],
                        'option'       => json_encode($item['option'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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
    public function groupSave(Request $request): Response
    {
        $id          = $request->post('id/d', 0);
        $title       = trim($request->post('title/s', ''));
        $name        = trim($request->post('name/s', ''));
        $description = trim($request->post('description/s', ''));
        $sort        = $request->post('sort/d', 0);
        $status      = $request->post('status/d', 1);

        // 基础校验
        if ($title === '') {
            return json(['code' => 1, 'msg' => '分组名称不能为空']);
        }
        if ($name === '') {
            return json(['code' => 1, 'msg' => '唯一标识码不能为空']);
        }
        if (!preg_match('/^[a-z][a-z0-9_]{1,49}$/', $name)) {
            return json(['code' => 1, 'msg' => '标识码只能由小写字母、数字、下划线组成，且以小写字母开头（2-50 位）']);
        }

        // 唯一性校验
        $existQuery = Db::name('slider_group')->where('name', $name);
        if ($id > 0) {
            $existQuery->where('id', '<>', $id);
        }
        if ($existQuery->count() > 0) {
            return json(['code' => 1, 'msg' => '标识码「' . $name . '」已存在，请更换']);
        }

        $data = [
            'title'       => $title,
            'name'        => $name,
            'description' => mb_substr($description, 0, 500),
            'sort'        => $sort,
            'status'      => $status,
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
    public function groupDelete(Request $request): Response
    {
        $id = $request->post('id/d', 0);
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
    public function itemSave(Request $request): Response
    {
        $id          = $request->post('id/d', 0);
        $groupId     = $request->post('group_id/d', 0);
        $title       = $request->post('title/s');
        $description = $request->post('description/s', '');
        $startTime   = $this->normalizeDatetime($request->post('start_time/s', ''));
        $endTime     = $this->normalizeDatetime($request->post('end_time/s', ''));
        $option      = trim($request->post('option/s', ''));
        $sort        = $request->post('sort/d', 0);
        $status      = $request->post('status/d', 1);
        $image       = $request->post('image/s', '');
        $url         = $request->post('url/s', '');

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
            'image'       => $image,
            'url'         => $url,
            'start_time'  => $startTime,
            'end_time'    => $endTime,
            'sort'        => $sort,
            'status'      => $status,
            'option'      => $option,
            'update_time' => date('Y-m-d H:i:s'),
        ];

        if ($id > 0) {
            Db::name('slider_item')->where('id', $id)->update($data);
        } else {
            $data['create_time'] = date('Y-m-d H:i:s');
            Db::name('slider_item')->json(['option'])->insert($data);
        }

        return json(['code' => 0, 'msg' => '保存成功']);
    }

    /* ============================================================
     *  删除子项
     * ============================================================ */
    public function itemDelete(Request $request): Response
    {
        $id = $request->post('id/d', 0);
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