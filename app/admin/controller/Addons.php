<?php
declare (strict_types=1);

namespace app\admin\controller;

use think\facade\View;
use think\facade\Filesystem;
use think\exception\ValidateException;
use think\Request;
use Symfony\Component\VarExporter\VarExporter;

class Addons
{
    // 插件根目录
    protected $addonsPath;

    public function __construct()
    {
        // 使用 root_path() 替代 base_path()
        $this->addonsPath = root_path() . 'addons' . DIRECTORY_SEPARATOR;
    }

    /**
     * 插件列表
     */
    public function index()
    {
        $dirs = glob($this->addonsPath . '*', GLOB_ONLYDIR);
        $list = [];
        foreach ($dirs as $dir) {
            $name = basename($dir);
            $configFile = $dir . DIRECTORY_SEPARATOR . 'config.php';
            if (is_file($configFile)) {
                $config = include $configFile;
                $list[] = [
                    'name'    => $name,
                    'title'   => $config['title'] ?? $name,
                    'status'  => $this->getPluginStatus($name),
                    'version' => $config['version'] ?? '1.0.0',
                ];
            }
        }
        View::assign('list', $list);
        return View::fetch();
    }

    /**
     * 获取插件状态（示例：从数据库读取，这里简化）
     */
    protected function getPluginStatus($name): int
    {
        // 假设有数据库表存储，此处返回1表示启用
        return 1;
    }

    /**
     * 配置表单页面
     */
    public function config($name)
    {
        // 安全性检查，防止目录遍历
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $name)) {
            throw new \think\exception\HttpException(400, '插件名称非法');
        }

        $configFile = $this->addonsPath . $name . DIRECTORY_SEPARATOR . 'config.php';
        if (!is_file($configFile)) {
            throw new \think\exception\HttpException(404, '插件配置不存在');
        }

        $config = include $configFile;
        
        // 预处理：为每个配置项添加 layui_rule 字段
        foreach ($config as &$item) {
            if (isset($item['rule']) && !empty($item['rule'])) {
                $item['layui_rule'] = str_replace('|', ',', $item['rule']);
            } else {
                $item['layui_rule'] = '';
            }
            
            // 确保所有字段都存在，避免模板报错
            if (!isset($item['options'])) {
                $item['options'] = [];
            }
            if (!isset($item['tips'])) {
                $item['tips'] = '';
            }
            if (!isset($item['group'])) {
                $item['group'] = '未分组';
            }
            if (!isset($item['sort'])) {
                $item['sort'] = 0;
            }
        }
        
        $grouped = $this->groupConfig($config);

        View::assign('name', $name);
        View::assign('grouped', $grouped);
        return View::fetch();
    }

    /**
     * 分组整理配置项
     */
    protected function groupConfig(array $config): array
    {
        $groups = [];
        foreach ($config as $key => $item) {
            $group = $item['group'] ?? '未分组';
            $sort  = $item['sort'] ?? 0;
            $groups[$group][$key] = $item;
        }
        // 对每组内的项按 sort 排序
        foreach ($groups as &$items) {
            uasort($items, function ($a, $b) {
                return ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0);
            });
        }
        return $groups;
    }

    /**
     * 保存配置 - 使用 VarExporter
     */
    public function saveConfig(Request $request)
    {
        $name = $request->post('name');
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $name)) {
            return json(['code' => 0, 'msg' => '插件名称非法']);
        }

        $configFile = $this->addonsPath . $name . DIRECTORY_SEPARATOR . 'config.php';
        if (!is_file($configFile)) {
            return json(['code' => 0, 'msg' => '配置文件不存在']);
        }

        // 获取原始配置结构
        $origin = include $configFile;
        $postData = $request->post();

        // 遍历原始配置，更新 value
        foreach ($origin as $key => &$item) {
            if (isset($postData[$key])) {
                $val = $postData[$key];
                switch ($item['type']) {
                    case 'checkbox':
                        $val = is_array($val) ? $val : [];
                        break;
                    case 'bool':
                        // 开关提交时若选中为 'on'，否则不存在，统一转为布尔
                        $val = isset($postData[$key]) && $postData[$key] === 'on';
                        break;
                    case 'number':
                        // 数字类型，前端提交的是字符串，需要转换为整数或者浮点数
                       if (is_numeric($val)) {
                            $val = str_contains($val, '.') ? floatval($val) : intval($val);
                       } else {
                            $val = 0;
                       }
                        break;
                    case 'array':
                        // 前端提交的是 JSON 字符串（由隐藏域组装）
                        if (is_string($val)) {
                            $decoded = json_decode($val, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $val = $decoded;
                            } else {
                                return json(['code' => 0, 'msg' => '数组格式无效']);
                            }
                        }
                        break;
                    case 'multi_array':
                        // 同样接收 JSON 字符串
                        if (is_string($val)) {
                            $decoded = json_decode($val, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $val = $decoded;
                            } else {
                                return json(['code' => 0, 'msg' => '多维数组 JSON 格式无效']);
                            }
                        }
                        break;
                    case 'images':
                    case 'files':
                        // 图片和文件列表，前端提交的是 JSON 字符串
                        if (is_string($val)) {
                            $decoded = json_decode($val, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $val = $decoded;
                            } else {
                                $val = [];
                            }
                        }
                        break;
                    default:
                        // 其他类型保持不变
                        break;
                }
                $item['value'] = $val;
            }
        }

        // 使用 VarExporter 导出数组
        try {
            $exported = VarExporter::export($origin);
            $content = '<?php' . PHP_EOL . PHP_EOL . 'return ' . $exported . ';' . PHP_EOL;
            
            if (file_put_contents($configFile, $content) !== false) {
                return json(['code' => 1, 'msg' => '保存成功']);
            } else {
                return json(['code' => 0, 'msg' => '保存失败，请检查目录权限']);
            }
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '保存失败：' . $e->getMessage()]);
        }
    }

    /**
     * 文件上传接口（供 LayUI 上传组件调用）
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');
        if (!$file) {
            return json(['code' => 0, 'msg' => '未上传文件']);
        }

        // 验证文件类型等（可根据需要扩展）
        try {
            validate(['file' => 'file|image|size:2048'])->check([$file]);
        } catch (ValidateException $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        }

        // 存储到 public/uploads/插件名/日期/
        $pluginName = $request->post('plugin') ?? 'default';
        $savePath = 'uploads/' . $pluginName . '/' . date('Ymd') . '/';
        $info = Filesystem::disk('public')->putFile($savePath, $file);
        if ($info) {
            $url = '/storage/' . $info; // 根据实际访问路径调整
            return json(['code' => 0, 'msg' => '上传成功', 'data' => ['url' => $url]]);
        } else {
            return json(['code' => 1, 'msg' => '上传失败']);
        }
    }
}